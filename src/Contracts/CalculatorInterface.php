<?php

declare(strict_types=1);

namespace Afrt\Core\Contracts;

use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\DTO\WorkloadBreakdown;
use Afrt\Core\ValueObjects\AcademicNormConfig;

interface CalculatorInterface
{
    /**
     * Compute academic workload breakdown for a subject across registered student cohorts.
     *
     * @param CurriculumSubject $subject Curriculum parameters & curricular hours
     * @param int $streamCount Number of lecture streams (S >= 1)
     * @param int $groupCount Number of academic groups (G >= 1)
     * @param int $subgroupCount Number of laboratory/practical subgroups (L >= G)
     * @param int $studentCount Total number of enrolled students (N >= 1)
     * @param AcademicNormConfig|null $config Optional custom norms; defaults to statutory standard
     * @return WorkloadBreakdown Computed hours disaggregated by teaching and supervisory activity
     */
    public function calculateSubjectWorkload(
        CurriculumSubject $subject,
        int $streamCount,
        int $groupCount,
        int $subgroupCount,
        int $studentCount,
        ?AcademicNormConfig $config = null
    ): WorkloadBreakdown;
}
