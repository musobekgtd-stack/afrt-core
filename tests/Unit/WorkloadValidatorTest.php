<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Unit;

use Afrt\Core\DTO\AllocationRequest;
use Afrt\Core\DTO\FacultyMember;
use Afrt\Core\Services\WorkloadValidator;
use Afrt\Core\ValueObjects\AcademicNormConfig;
use PHPUnit\Framework\TestCase;

final class WorkloadValidatorTest extends TestCase
{
    private WorkloadValidator $validator;
    private AcademicNormConfig $config;

    protected function setUp(): void
    {
        $this->validator = new WorkloadValidator();
        $this->config = AcademicNormConfig::createDefault();
    }

    /**
     * VAL-001: Standard allocation within quota
     * Faculty 1.0 FTE (750h quota), current 400h, request 100h -> resulting 500h (valid)
     */
    public function testStandardAllocationWithinQuota(): void
    {
        $faculty = new FacultyMember('FAC-1', 'Test Faculty', 'docent', 1.0, 400.0, 200.0);
        $request = new AllocationRequest('CS-101', 'Intro to CS', 'lecture', 100.0, true);

        $result = $this->validator->validateAllocation($faculty, $request, $this->config);

        $this->assertTrue($result->isValid());
        $this->assertSame(750.0, $result->getMaxPermittedHours());
        $this->assertSame(500.0, $result->getResultingHours());
        $this->assertSame(250.0, $result->getRemainingHours());
        $this->assertEmpty($result->getViolations());
    }

    /**
     * VAL-002: Quota ceiling breach rejection
     * Faculty 0.5 FTE (375h quota), current 300h, request 100h -> resulting 400h > 375h (invalid)
     */
    public function testQuotaCeilingBreachRejection(): void
    {
        $faculty = new FacultyMember('FAC-2', 'Part-Time Faculty', 'assistant', 0.5, 300.0, 150.0);
        $request = new AllocationRequest('CS-102', 'Programming', 'practical', 100.0, true);

        $result = $this->validator->validateAllocation($faculty, $request, $this->config);

        $this->assertFalse($result->isValid());
        $this->assertSame(375.0, $result->getMaxPermittedHours());
        $this->assertSame(400.0, $result->getResultingHours());
        $this->assertCount(1, $result->getViolations());
        $this->assertStringContainsString('Workload quota exceeded', $result->getViolations()[0]);
    }

    /**
     * VAL-003: High utilization saturation warning (>= 95%)
     * Faculty 1.0 FTE (750h quota), current 700h, request 20h -> 720h (96% saturation)
     */
    public function testHighUtilizationSaturationWarning(): void
    {
        $faculty = new FacultyMember('FAC-3', 'Senior Faculty', 'professor', 1.0, 700.0, 350.0);
        $request = new AllocationRequest('CS-103', 'Seminar', 'seminar', 20.0, true);

        $result = $this->validator->validateAllocation($faculty, $request, $this->config);

        $this->assertTrue($result->isValid());
        $this->assertSame(720.0, $result->getResultingHours());
        $this->assertSame(96.0, $result->getFteUtilizationPercent());
        $this->assertNotEmpty($result->getWarnings());
        $this->assertStringContainsString('saturation', $result->getWarnings()[0]);
    }

    /**
     * VAL-004: Maximum FTE ceiling check (> 1.50)
     */
    public function testAppointmentCeilingViolation(): void
    {
        $faculty = new FacultyMember('FAC-4', 'Overloaded Faculty', 'assistant', 1.75, 100.0, 50.0);
        $request = new AllocationRequest('CS-104', 'Lab', 'lab', 50.0, true);

        $result = $this->validator->validateAllocation($faculty, $request, $this->config);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getViolations());
        $this->assertStringContainsString('exceeds maximum allowed ceiling', $result->getViolations()[0]);
    }

    /**
     * VAL-005: Department-wide multi-faculty batch validation
     */
    public function testDepartmentWideValidation(): void
    {
        $facultyList = [
            new FacultyMember('F1', 'Faculty 1', 'docent', 1.0, 600.0, 300.0),
            new FacultyMember('F2', 'Faculty 2', 'docent', 1.0, 730.0, 380.0),
        ];

        $allocations = [
            'F1' => new AllocationRequest('S1', 'Sub 1', 'lecture', 50.0, true),
            'F2' => new AllocationRequest('S2', 'Sub 2', 'lecture', 50.0, true), // 730 + 50 = 780 > 750
        ];

        $results = $this->validator->validateDepartmentWorkload($facultyList, $allocations, $this->config);

        $this->assertTrue($results['F1']->isValid());
        $this->assertFalse($results['F2']->isValid());
    }
}
