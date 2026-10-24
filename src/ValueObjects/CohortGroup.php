<?php

declare(strict_types=1);

namespace Afrt\Core\ValueObjects;

use Afrt\Core\Exceptions\InvalidCohortConfigurationException;

/**
 * Immutable value object representing an academic student cohort group.
 */
final class CohortGroup
{
    private string $id;
    private string $name;
    private int $studentCount;
    private string $language;
    private int $semester;
    private string $educationLevel;

    public function __construct(
        string $id,
        string $name,
        int $studentCount,
        string $language = 'uz',
        int $semester = 1,
        string $educationLevel = 'bachelor'
    ) {
        $cleanId = trim($id);
        $cleanName = trim($name);

        if ($cleanId === '' || $cleanName === '') {
            throw new InvalidCohortConfigurationException("Cohort group ID and name cannot be blank.");
        }
        if ($studentCount < 1) {
            throw new InvalidCohortConfigurationException("Cohort group '{$cleanName}' must have at least 1 student. Given: {$studentCount}");
        }
        if ($semester < 1 || $semester > 12) {
            throw new InvalidCohortConfigurationException("Semester must be between 1 and 12. Given: {$semester}");
        }

        $this->id = $cleanId;
        $this->name = $cleanName;
        $this->studentCount = $studentCount;
        $this->language = strtolower(trim($language));
        $this->semester = $semester;
        $this->educationLevel = strtolower(trim($educationLevel));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStudentCount(): int
    {
        return $this->studentCount;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getSemester(): int
    {
        return $this->semester;
    }

    public function getEducationLevel(): string
    {
        return $this->educationLevel;
    }

    public function isCompatibleWith(CohortGroup $other): bool
    {
        return $this->language === $other->language
            && $this->semester === $other->semester
            && $this->educationLevel === $other->educationLevel;
    }
}
