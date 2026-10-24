<?php

declare(strict_types=1);

namespace Afrt\Core\Services;

use Afrt\Core\Contracts\ValidatorInterface;
use Afrt\Core\DTO\AllocationRequest;
use Afrt\Core\DTO\FacultyMember;
use Afrt\Core\DTO\ValidationResult;
use Afrt\Core\ValueObjects\AcademicNormConfig;

/**
 * Core validation engine for faculty appointment workload quotas and limits.
 * Standalone reproduction of AFRT faculty capacity and allocation constraints.
 */
final class WorkloadValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateAllocation(
        FacultyMember $faculty,
        AllocationRequest $request,
        ?AcademicNormConfig $config = null
    ): ValidationResult {
        $cfg = $config ?? AcademicNormConfig::createDefault();
        $prec = $cfg->getRoundingPrecision();

        $maxPermittedHours = $cfg->computeMaxHoursForFte($faculty->getFte());
        $currentHours = $faculty->getCurrentAllocatedHours();
        $requestedHours = $request->getHours();
        $resultingHours = round($currentHours + $requestedHours, $prec);
        $remainingHours = round($maxPermittedHours - $resultingHours, $prec);

        $utilizationPercent = $maxPermittedHours > 0.0
            ? round(($resultingHours / $maxPermittedHours) * 100.0, 2)
            : 0.0;

        $violations = [];
        $warnings = [];

        // 1. Quota Check
        if ($resultingHours > $maxPermittedHours) {
            $overage = round($resultingHours - $maxPermittedHours, $prec);
            $violations[] = sprintf(
                "Workload quota exceeded for %s (%s). Max allowed for %.2f FTE is %.2fh; resulting is %.2fh (overage of %.2fh).",
                $faculty->getName(),
                $faculty->getId(),
                $faculty->getFte(),
                $maxPermittedHours,
                $resultingHours,
                $overage
            );
        }

        // 2. Appointment Ceiling Check
        if ($faculty->getFte() > $cfg->getMaxFtePerFaculty()) {
            $violations[] = sprintf(
                "Faculty member appointment rate (%.2f FTE) exceeds maximum allowed ceiling (%.2f FTE).",
                $faculty->getFte(),
                $cfg->getMaxFtePerFaculty()
            );
        }

        // 3. Near-saturation Warning
        if ($resultingHours <= $maxPermittedHours && $utilizationPercent >= 95.0) {
            $warnings[] = sprintf(
                "Workload near saturation: faculty member is at %.2f%% of maximum quota (%.2fh remaining).",
                $utilizationPercent,
                $remainingHours
            );
        }

        // 4. Auditorium Balance Warning
        $resultingAuditorium = $faculty->getCurrentAuditoriumHours() + ($request->isAuditorium() ? $requestedHours : 0.0);
        if ($resultingHours > 200.0) {
            $auditoriumRatio = $resultingAuditorium / $resultingHours;
            if ($auditoriumRatio > 0.85) {
                $warnings[] = sprintf(
                    "High auditorium concentration: auditorium load represents %.1f%% of total workload.",
                    $auditoriumRatio * 100.0
                );
            } elseif ($auditoriumRatio < 0.20) {
                $warnings[] = sprintf(
                    "Low auditorium representation: auditorium load represents only %.1f%% of total workload.",
                    $auditoriumRatio * 100.0
                );
            }
        }

        $isValid = empty($violations);

        return new ValidationResult(
            $isValid,
            $maxPermittedHours,
            $currentHours,
            $requestedHours,
            $resultingHours,
            $remainingHours,
            $utilizationPercent,
            $violations,
            $warnings
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateDepartmentWorkload(
        array $facultyList,
        array $allocations,
        ?AcademicNormConfig $config = null
    ): array {
        $results = [];

        foreach ($facultyList as $faculty) {
            if (!$faculty instanceof FacultyMember) {
                continue;
            }

            $facultyAllocations = $allocations[$faculty->getId()] ?? [];
            if (!is_array($facultyAllocations)) {
                $facultyAllocations = [$facultyAllocations];
            }

            // Aggregate multiple requests for this faculty member if present
            $currentFaculty = $faculty;
            $combinedResult = null;

            if (empty($facultyAllocations)) {
                // Validate 0 additional hours to check current status
                $dummyRequest = new AllocationRequest(
                    'STATUS_CHECK',
                    'Status Check',
                    'lecture',
                    0.0001,
                    false
                );
                // Adjust result without the dummy 0.0001
                $res = $this->validateAllocation($faculty, $dummyRequest, $config);
                $combinedResult = new ValidationResult(
                    $faculty->getCurrentAllocatedHours() <= ($config ?? AcademicNormConfig::createDefault())->computeMaxHoursForFte($faculty->getFte()),
                    $res->getMaxPermittedHours(),
                    $faculty->getCurrentAllocatedHours(),
                    0.0,
                    $faculty->getCurrentAllocatedHours(),
                    $res->getMaxPermittedHours() - $faculty->getCurrentAllocatedHours(),
                    $res->getMaxPermittedHours() > 0 ? round(($faculty->getCurrentAllocatedHours() / $res->getMaxPermittedHours()) * 100, 2) : 0,
                    $faculty->getCurrentAllocatedHours() > $res->getMaxPermittedHours() ? ["Current allocation exceeds quota."] : [],
                    []
                );
            } else {
                foreach ($facultyAllocations as $alloc) {
                    if ($alloc instanceof AllocationRequest) {
                        $combinedResult = $this->validateAllocation($currentFaculty, $alloc, $config);
                        $currentFaculty = $currentFaculty->withAdditionalHours(
                            $alloc->getHours(),
                            $alloc->isAuditorium() ? $alloc->getHours() : 0.0
                        );
                    }
                }
            }

            $results[$faculty->getId()] = $combinedResult;
        }

        return $results;
    }
}
