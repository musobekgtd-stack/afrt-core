<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

use Afrt\Core\Exceptions\InvalidCurriculumNormException;

/**
 * Data transfer object representing an allocation request for teaching/supervisory hours.
 */
final class AllocationRequest
{
    private string $subjectCode;
    private string $subjectName;
    private string $activityType;
    private float $hours;
    private bool $isAuditorium;
    /** @var string[] */
    private array $assignedGroups;

    /**
     * @param string[] $assignedGroups
     */
    public function __construct(
        string $subjectCode,
        string $subjectName,
        string $activityType,
        float $hours,
        bool $isAuditorium = true,
        array $assignedGroups = []
    ) {
        $cleanCode = trim($subjectCode);
        $cleanName = trim($subjectName);

        if ($cleanCode === '' || $cleanName === '') {
            throw new InvalidCurriculumNormException("Subject code and name cannot be blank.");
        }
        if ($hours <= 0.0) {
            throw new InvalidCurriculumNormException("Allocation hours must be greater than zero. Given: {$hours}");
        }

        $this->subjectCode = $cleanCode;
        $this->subjectName = $cleanName;
        $this->activityType = strtolower(trim($activityType));
        $this->hours = round($hours, 2);
        $this->isAuditorium = $isAuditorium;
        $this->assignedGroups = $assignedGroups;
    }

    public function getSubjectCode(): string
    {
        return $this->subjectCode;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    public function getActivityType(): string
    {
        return $this->activityType;
    }

    public function getHours(): float
    {
        return $this->hours;
    }

    public function isAuditorium(): bool
    {
        return $this->isAuditorium;
    }

    /**
     * @return string[]
     */
    public function getAssignedGroups(): array
    {
        return $this->assignedGroups;
    }
}
