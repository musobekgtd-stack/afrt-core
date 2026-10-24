<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Integration;

use Afrt\Core\DTO\AllocationRequest;
use Afrt\Core\Services\CohortStreamBundler;
use Afrt\Core\Services\SubjectDiscrepancyMatcher;
use Afrt\Core\Services\WorkloadCalculator;
use Afrt\Core\Services\WorkloadValidator;
use Afrt\Core\Tests\Fixtures\SyntheticDepartmentFactory;
use Afrt\Core\ValueObjects\AcademicNormConfig;
use PHPUnit\Framework\TestCase;

final class DepartmentWorkflowTest extends TestCase
{
    public function testEndToEndDepartmentWorkflow(): void
    {
        $calculator = new WorkloadCalculator();
        $bundler = new CohortStreamBundler();
        $validator = new WorkloadValidator();
        $matcher = new SubjectDiscrepancyMatcher();
        $config = AcademicNormConfig::createDefault();

        // 1. Load synthetic fixtures
        $curriculum = SyntheticDepartmentFactory::createCurriculum();
        $cohorts = SyntheticDepartmentFactory::createCohorts();
        $faculty = SyntheticDepartmentFactory::createFaculty();

        // 2. Bundle cohorts into streams
        $partition = $bundler->bundleCohorts($cohorts, maxStreamSize: 120, maxGroupsPerStream: 4);
        $this->assertGreaterThanOrEqual(1, $partition->getStreamCount());
        $this->assertGreaterThan(0, $partition->getSubgroupCount());

        // 3. Compute workload for first curriculum subject
        $csSubject = $curriculum[0]; // SYN-CS101: 30h lec, 30h prac, 30h lab, 2h coursework, 4 bach thesis
        $breakdown = $calculator->calculateSubjectWorkload(
            $csSubject,
            streamCount: 1,
            groupCount: 4,
            subgroupCount: 8,
            studentCount: 96,
            config: $config
        );

        $this->assertSame(30.0, $breakdown->getLectureHours()); // 30 * 1
        $this->assertSame(120.0, $breakdown->getPracticalHours()); // 30 * 4
        $this->assertSame(240.0, $breakdown->getLabHours()); // 30 * 8
        $this->assertSame(192.0, $breakdown->getCourseworkHours()); // 96 * 2
        $this->assertSame(60.0, $breakdown->getThesisHours()); // 4 * 15
        $this->assertSame(4.0, $breakdown->getExamHours());
        $this->assertSame(646.0, $breakdown->getTotalHours());

        // 4. Validate allocation of lecture hours to Prof. Al-Khwarizmi (1.0 FTE, 300h current)
        $lectureRequest = new AllocationRequest(
            $csSubject->getCode(),
            $csSubject->getName(),
            'lecture',
            $breakdown->getLectureHours(),
            isAuditorium: true
        );
        $facultyMember = $faculty[0];
        $valResult = $validator->validateAllocation($facultyMember, $lectureRequest, $config);

        $this->assertTrue($valResult->isValid());
        $this->assertSame(330.0, $valResult->getResultingHours());
        $this->assertSame(420.0, $valResult->getRemainingHours());

        // 5. Title reconciliation against timetable
        $curriculumTitles = [$csSubject->getName()];
        $timetableTitles = ['Introduction to Computer Systems (1-modul)'];
        $discReport = $matcher->reconcileTitles($curriculumTitles, $timetableTitles);

        $this->assertSame(1, $discReport->getSubpartMatches());
        $this->assertSame(100.0, $discReport->getMatchRate());
    }
}
