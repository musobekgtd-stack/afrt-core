<?php

declare(strict_types=1);

namespace Afrt\Core\ValueObjects;

use Afrt\Core\Exceptions\InvalidCurriculumNormException;

/**
 * Immutable configuration value object encapsulating academic workload statutory norms,
 * standard calculation ratios, and institutional threshold constraints.
 */
final class AcademicNormConfig
{
    private float $baseQuotaPerFte;
    private float $maxFtePerFaculty;
    private float $bachelorThesisNorm;
    private float $masterThesisNorm;
    private float $courseworkNormPerStudent;
    private float $phdSupervisionNorm;
    private int $subgroupStudentThreshold;
    private int $defaultStreamMaxStudents;
    private int $defaultStreamMaxGroups;
    private int $roundingPrecision;

    public function __construct(
        float $baseQuotaPerFte = 750.0,
        float $maxFtePerFaculty = 1.50,
        float $bachelorThesisNorm = 15.0,
        float $masterThesisNorm = 25.0,
        float $courseworkNormPerStudent = 2.0,
        float $phdSupervisionNorm = 50.0,
        int $subgroupStudentThreshold = 20,
        int $defaultStreamMaxStudents = 120,
        int $defaultStreamMaxGroups = 4,
        int $roundingPrecision = 2
    ) {
        if ($baseQuotaPerFte <= 0.0) {
            throw new InvalidCurriculumNormException("Base quota per FTE must be greater than zero. Given: {$baseQuotaPerFte}");
        }
        if ($maxFtePerFaculty <= 0.0 || $maxFtePerFaculty > 2.0) {
            throw new InvalidCurriculumNormException("Max FTE per faculty must be in range (0.0, 2.0]. Given: {$maxFtePerFaculty}");
        }
        if ($bachelorThesisNorm < 0.0 || $masterThesisNorm < 0.0) {
            throw new InvalidCurriculumNormException("Thesis norms cannot be negative.");
        }
        if ($subgroupStudentThreshold < 1) {
            throw new InvalidCurriculumNormException("Subgroup division threshold must be at least 1 student.");
        }

        $this->baseQuotaPerFte = $baseQuotaPerFte;
        $this->maxFtePerFaculty = $maxFtePerFaculty;
        $this->bachelorThesisNorm = $bachelorThesisNorm;
        $this->masterThesisNorm = $masterThesisNorm;
        $this->courseworkNormPerStudent = $courseworkNormPerStudent;
        $this->phdSupervisionNorm = $phdSupervisionNorm;
        $this->subgroupStudentThreshold = $subgroupStudentThreshold;
        $this->defaultStreamMaxStudents = $defaultStreamMaxStudents;
        $this->defaultStreamMaxGroups = $defaultStreamMaxGroups;
        $this->roundingPrecision = $roundingPrecision;
    }

    public static function createDefault(): self
    {
        return new self();
    }

    public function getBaseQuotaPerFte(): float
    {
        return $this->baseQuotaPerFte;
    }

    public function getMaxFtePerFaculty(): float
    {
        return $this->maxFtePerFaculty;
    }

    public function getBachelorThesisNorm(): float
    {
        return $this->bachelorThesisNorm;
    }

    public function getMasterThesisNorm(): float
    {
        return $this->masterThesisNorm;
    }

    public function getCourseworkNormPerStudent(): float
    {
        return $this->courseworkNormPerStudent;
    }

    public function getPhdSupervisionNorm(): float
    {
        return $this->phdSupervisionNorm;
    }

    public function getSubgroupStudentThreshold(): int
    {
        return $this->subgroupStudentThreshold;
    }

    public function getDefaultStreamMaxStudents(): int
    {
        return $this->defaultStreamMaxStudents;
    }

    public function getDefaultStreamMaxGroups(): int
    {
        return $this->defaultStreamMaxGroups;
    }

    public function getRoundingPrecision(): int
    {
        return $this->roundingPrecision;
    }

    /**
     * Compute maximum allowable workload hours for a given FTE appointment (stavka).
     */
    public function computeMaxHoursForFte(float $fte): float
    {
        return round($this->baseQuotaPerFte * $fte, $this->roundingPrecision);
    }
}
