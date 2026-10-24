<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

/**
 * Data transfer object encapsulating computed academic workload hours.
 */
final class WorkloadBreakdown
{
    private string $subjectCode;
    private string $subjectName;
    private float $lectureHours;
    private float $practicalHours;
    private float $labHours;
    private float $seminarHours;
    private float $enterprisePracticeHours;
    private float $courseworkHours;
    private float $thesisHours;
    private float $examHours;
    private float $internshipHours;
    private float $doctoralHours;
    private float $auditoriumHours;
    private float $nonAuditoriumHours;
    private float $totalHours;

    public function __construct(
        string $subjectCode,
        string $subjectName,
        float $lectureHours,
        float $practicalHours,
        float $labHours,
        float $seminarHours,
        float $enterprisePracticeHours,
        float $courseworkHours,
        float $thesisHours,
        float $examHours,
        float $internshipHours,
        float $doctoralHours = 0.0
    ) {
        $this->subjectCode = $subjectCode;
        $this->subjectName = $subjectName;
        $this->lectureHours = round($lectureHours, 2);
        $this->practicalHours = round($practicalHours, 2);
        $this->labHours = round($labHours, 2);
        $this->seminarHours = round($seminarHours, 2);
        $this->enterprisePracticeHours = round($enterprisePracticeHours, 2);
        $this->courseworkHours = round($courseworkHours, 2);
        $this->thesisHours = round($thesisHours, 2);
        $this->examHours = round($examHours, 2);
        $this->internshipHours = round($internshipHours, 2);
        $this->doctoralHours = round($doctoralHours, 2);

        $this->auditoriumHours = round(
            $this->lectureHours + $this->practicalHours + $this->labHours + $this->seminarHours,
            2
        );

        $this->nonAuditoriumHours = round(
            $this->enterprisePracticeHours + $this->courseworkHours + $this->thesisHours
            + $this->examHours + $this->internshipHours + $this->doctoralHours,
            2
        );

        $this->totalHours = round($this->auditoriumHours + $this->nonAuditoriumHours, 2);
    }

    public function getSubjectCode(): string
    {
        return $this->subjectCode;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
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

    public function getCourseworkHours(): float
    {
        return $this->courseworkHours;
    }

    public function getThesisHours(): float
    {
        return $this->thesisHours;
    }

    public function getExamHours(): float
    {
        return $this->examHours;
    }

    public function getInternshipHours(): float
    {
        return $this->internshipHours;
    }

    public function getDoctoralHours(): float
    {
        return $this->doctoralHours;
    }

    public function getAuditoriumHours(): float
    {
        return $this->auditoriumHours;
    }

    public function getNonAuditoriumHours(): float
    {
        return $this->nonAuditoriumHours;
    }

    public function getTotalHours(): float
    {
        return $this->totalHours;
    }

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'subjectCode' => $this->subjectCode,
            'subjectName' => $this->subjectName,
            'lectureHours' => $this->lectureHours,
            'practicalHours' => $this->practicalHours,
            'labHours' => $this->labHours,
            'seminarHours' => $this->seminarHours,
            'enterprisePracticeHours' => $this->enterprisePracticeHours,
            'courseworkHours' => $this->courseworkHours,
            'thesisHours' => $this->thesisHours,
            'examHours' => $this->examHours,
            'internshipHours' => $this->internshipHours,
            'doctoralHours' => $this->doctoralHours,
            'auditoriumHours' => $this->auditoriumHours,
            'nonAuditoriumHours' => $this->nonAuditoriumHours,
            'totalHours' => $this->totalHours,
        ];
    }
}
