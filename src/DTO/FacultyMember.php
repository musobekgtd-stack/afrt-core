<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

use Afrt\Core\Exceptions\InvalidCurriculumNormException;

/**
 * Data transfer object representing a faculty member profile and appointment capacity.
 */
final class FacultyMember
{
    private string $id;
    private string $name;
    private string $academicRank;
    private float $fte;
    private float $currentAllocatedHours;
    private float $currentAuditoriumHours;

    public function __construct(
        string $id,
        string $name,
        string $academicRank = 'assistant',
        float $fte = 1.0,
        float $currentAllocatedHours = 0.0,
        float $currentAuditoriumHours = 0.0
    ) {
        $cleanId = trim($id);
        $cleanName = trim($name);

        if ($cleanId === '' || $cleanName === '') {
            throw new InvalidCurriculumNormException("Faculty ID and name cannot be blank.");
        }
        if ($fte <= 0.0 || $fte > 2.0) {
            throw new InvalidCurriculumNormException("Faculty FTE must be in range (0.0, 2.0]. Given: {$fte}");
        }
        if ($currentAllocatedHours < 0.0 || $currentAuditoriumHours < 0.0) {
            throw new InvalidCurriculumNormException("Current allocated hours cannot be negative.");
        }

        $this->id = $cleanId;
        $this->name = $cleanName;
        $this->academicRank = trim($academicRank);
        $this->fte = $fte;
        $this->currentAllocatedHours = round($currentAllocatedHours, 2);
        $this->currentAuditoriumHours = round($currentAuditoriumHours, 2);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAcademicRank(): string
    {
        return $this->academicRank;
    }

    public function getFte(): float
    {
        return $this->fte;
    }

    public function getCurrentAllocatedHours(): float
    {
        return $this->currentAllocatedHours;
    }

    public function getCurrentAuditoriumHours(): float
    {
        return $this->currentAuditoriumHours;
    }

    public function withAdditionalHours(float $totalHours, float $auditoriumHours): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->academicRank,
            $this->fte,
            $this->currentAllocatedHours + $totalHours,
            $this->currentAuditoriumHours + $auditoriumHours
        );
    }
}
