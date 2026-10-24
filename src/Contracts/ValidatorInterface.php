<?php

declare(strict_types=1);

namespace Afrt\Core\Contracts;

use Afrt\Core\DTO\AllocationRequest;
use Afrt\Core\DTO\FacultyMember;
use Afrt\Core\DTO\ValidationResult;
use Afrt\Core\ValueObjects\AcademicNormConfig;

interface ValidatorInterface
{
    /**
     * Validate an academic workload allocation request against faculty quotas,
     * FTE appointment limits, and pedagogical balance constraints.
     *
     * @param FacultyMember $faculty The faculty member receiving the allocation
     * @param AllocationRequest $request The requested workload allocation
     * @param AcademicNormConfig|null $config Optional custom quota configuration
     * @return ValidationResult Validation status, violations, warnings, and capacity metrics
     */
    public function validateAllocation(
        FacultyMember $faculty,
        AllocationRequest $request,
        ?AcademicNormConfig $config = null
    ): ValidationResult;

    /**
     * Validate department-wide workload balance across all faculty members.
     *
     * @param FacultyMember[] $facultyList List of all department faculty members
     * @param AllocationRequest[] $allocations List of allocations indexed by faculty ID
     * @param AcademicNormConfig|null $config Optional custom configuration
     * @return ValidationResult[] Validation results indexed by faculty ID
     */
    public function validateDepartmentWorkload(
        array $facultyList,
        array $allocations,
        ?AcademicNormConfig $config = null
    ): array;
}
