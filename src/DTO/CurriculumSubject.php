<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

use Afrt\Core\Exceptions\InvalidCurriculumNormException;

/**
 * Data transfer object representing an academic subject with curricular hours according to the curriculum plan.
 */
final class CurriculumSubject
{
    private string $code;
    private string $name;
    private float $lectureHours;
    private float $practicalHours;
    private float $labHours;
    private float $seminarHours;
    private float $enterprisePracticeHours;
    private float $courseworkHoursPerStudent;
    private int $bachelorThesisStudents;
    private int $masterThesisStudents;
    private int $phdStudents;
    private float $examHours;
    private float $internshipHours;
    private string $educationLevel;

    public function __construct(
        string $code,
        string $name,
        float $lectureHours = 0.0,
        float $practicalHours = 0.0,
        float $labHours = 0.0,
        float $seminarHours = 0.0,
        float $enterprisePracticeHours = 0.0,
        float $courseworkHoursPerStudent = 0.0,
        int $bachelorThesisStudents = 0,
        int $masterThesisStudents = 0,
        int $phdStudents = 0,
        float $examHours = 0.0,
        float $internshipHours = 0.0,
        string $educationLevel = 'bachelor'
    ) {
        $cleanCode = trim($code);
        $cleanName = trim($name);

        if ($cleanCode === '' || $cleanName === '') {
            throw new InvalidCurriculumNormException("Curriculum subject code and name cannot be empty.");
        }
        if ($lectureHours < 0.0 || $practicalHours < 0.0 || $labHours < 0.0 || $seminarHours < 0.0 || $enterprisePracticeHours < 0.0) {
            throw new InvalidCurriculumNormException("Curricular teaching hours cannot be negative.");
        }
        if ($courseworkHoursPerStudent < 0.0 || $bachelorThesisStudents < 0 || $masterThesisStudents < 0 || $phdStudents < 0) {
            throw new InvalidCurriculumNormException("Supervisory hours/counts cannot be negative.");
        }

        $this->code = $cleanCode;
        $this->name = $cleanName;
        $this->lectureHours = $lectureHours;
        $this->practicalHours = $practicalHours;
        $this->labHours = $labHours;
        $this->seminarHours = $seminarHours;
        $this->enterprisePracticeHours = $enterprisePracticeHours;
        $this->courseworkHoursPerStudent = $courseworkHoursPerStudent;
        $this->bachelorThesisStudents = $bachelorThesisStudents;
        $this->masterThesisStudents = $masterThesisStudents;
        $this->phdStudents = $phdStudents;
        $this->examHours = $examHours;
        $this->internshipHours = $internshipHours;
        $this->educationLevel = strtolower(trim($educationLevel));
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLectureHours(): float
    {
        return $this->lectureHours;
    }

    public function getPracticalHours(): float
    {
        return $this->practicalHours;
    }

    public function getLabHours(): float
    {
        return $this->labHours;
    }

    public function getSeminarHours(): float
    {
        return $this->seminarHours;
    }

    public function getEnterprisePracticeHours(): float
    {
        return $this->enterprisePracticeHours;
    }

    public function getCourseworkHoursPerStudent(): float
    {
        return $this->courseworkHoursPerStudent;
    }

    public function getBachelorThesisStudents(): int
    {
        return $this->bachelorThesisStudents;
    }

    public function getMasterThesisStudents(): int
    {
        return $this->masterThesisStudents;
    }

    public function getPhdStudents(): int
    {
        return $this->phdStudents;
    }

    public function getExamHours(): float
    {
        return $this->examHours;
    }

    public function getInternshipHours(): float
    {
        return $this->internshipHours;
    }

    public function getEducationLevel(): string
    {
        return $this->educationLevel;
    }

    public function getCurricularAuditoriumHours(): float
    {
        return $this->lectureHours + $this->practicalHours + $this->labHours + $this->seminarHours;
    }
}
