<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Unit;

use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\Exceptions\InvalidCurriculumNormException;
use Afrt\Core\Services\WorkloadCalculator;
use Afrt\Core\ValueObjects\AcademicNormConfig;
use PHPUnit\Framework\TestCase;

final class WorkloadCalculatorTest extends TestCase
{
    private WorkloadCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new WorkloadCalculator();
    }

    /**
     * CALC-001: Standard lecture + practical calculation
     * H_lec = 30 * 2 = 60, H_prac = 30 * 4 = 120
     */
    public function testStandardLectureAndPracticalCalculation(): void
    {
        $subject = new CurriculumSubject(
            'TEST-001',
            'Test Fundamentals',
            lectureHours: 30.0,
            practicalHours: 30.0
        );

        $breakdown = $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 2,
            groupCount: 4,
            subgroupCount: 4,
            studentCount: 100
        );

        $this->assertSame(60.0, $breakdown->getLectureHours());
        $this->assertSame(120.0, $breakdown->getPracticalHours());
        $this->assertSame(0.0, $breakdown->getLabHours());
        $this->assertSame(180.0, $breakdown->getAuditoriumHours());
        $this->assertSame(180.0, $breakdown->getTotalHours());
    }

    /**
     * CALC-002: Subgroup scaling for laboratory hours
     * 4 academic groups split into 8 laboratory subgroups
     * H_lab = 36 * 8 = 288
     */
    public function testSubgroupScalingForLaboratoryHours(): void
    {
        $subject = new CurriculumSubject(
            'TEST-002',
            'Applied Laboratory Practice',
            labHours: 36.0
        );

        $breakdown = $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 1,
            groupCount: 4,
            subgroupCount: 8,
            studentCount: 100
        );

        $this->assertSame(288.0, $breakdown->getLabHours());
        $this->assertSame(288.0, $breakdown->getAuditoriumHours());
        $this->assertSame(288.0, $breakdown->getTotalHours());
    }

    /**
     * CALC-003: Supervisory load according to statutory norms
     * 6 bachelor theses (6 * 15h = 90h), 2 master theses (2 * 25h = 50h), 1 PhD (1 * 50h = 50h)
     * Total thesis & doctoral hours = 190h
     */
    public function testSupervisoryLoadStatutoryNorms(): void
    {
        $subject = new CurriculumSubject(
            'TEST-003',
            'Graduate Research Supervision',
            bachelorThesisStudents: 6,
            masterThesisStudents: 2,
            phdStudents: 1
        );

        $breakdown = $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 1,
            groupCount: 1,
            subgroupCount: 1,
            studentCount: 10
        );

        $this->assertSame(140.0, $breakdown->getThesisHours()); // 6*15 + 2*25 = 90 + 50 = 140
        $this->assertSame(50.0, $breakdown->getDoctoralHours());  // 1*50 = 50
        $this->assertSame(190.0, $breakdown->getNonAuditoriumHours());
        $this->assertSame(190.0, $breakdown->getTotalHours());
    }

    /**
     * CALC-004: Boundary: Zero hours and empty courses
     */
    public function testZeroHoursCurriculumSubject(): void
    {
        $subject = new CurriculumSubject(
            'ZERO-001',
            'Zero Hours Subject'
        );

        $breakdown = $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 1,
            groupCount: 1,
            subgroupCount: 1,
            studentCount: 20
        );

        $this->assertSame(0.0, $breakdown->getTotalHours());
        $this->assertSame(0.0, $breakdown->getAuditoriumHours());
        $this->assertSame(0.0, $breakdown->getNonAuditoriumHours());
    }

    /**
     * CALC-005: Input validation & exception handling
     */
    public function testInvalidSubgroupCountThrowsException(): void
    {
        $this->expectException(InvalidCurriculumNormException::class);

        $subject = new CurriculumSubject('ERR-001', 'Error Subject');
        // Subgroup count (2) is less than group count (4) -> illegal
        $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 1,
            groupCount: 4,
            subgroupCount: 2,
            studentCount: 50
        );
    }

    public function testCustomConfigurationNorms(): void
    {
        $customConfig = new AcademicNormConfig(
            baseQuotaPerFte: 800.0,
            bachelorThesisNorm: 20.0,
            masterThesisNorm: 30.0,
            courseworkNormPerStudent: 3.0
        );

        $subject = new CurriculumSubject(
            'CUST-001',
            'Custom Subject',
            courseworkHoursPerStudent: 3.0,
            bachelorThesisStudents: 2
        );

        $breakdown = $this->calculator->calculateSubjectWorkload(
            $subject,
            streamCount: 1,
            groupCount: 1,
            subgroupCount: 1,
            studentCount: 10,
            config: $customConfig
        );

        // Coursework: 10 * 3 = 30; Thesis: 2 * 20 = 40; Total = 70
        $this->assertSame(30.0, $breakdown->getCourseworkHours());
        $this->assertSame(40.0, $breakdown->getThesisHours());
        $this->assertSame(70.0, $breakdown->getTotalHours());
    }
}
