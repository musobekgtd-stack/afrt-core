<?php

declare(strict_types=1);

namespace Afrt\Core\Services;

use Afrt\Core\Contracts\CalculatorInterface;
use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\DTO\WorkloadBreakdown;
use Afrt\Core\Exceptions\InvalidCurriculumNormException;
use Afrt\Core\ValueObjects\AcademicNormConfig;

/**
 * Core computational engine for academic workload calculation.
 * Decoupled, standalone implementation reproducing the statutory workload aggregation logic of AFRT FanSoat.
 */
final class WorkloadCalculator implements CalculatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function calculateSubjectWorkload(
        CurriculumSubject $subject,
        int $streamCount,
        int $groupCount,
        int $subgroupCount,
        int $studentCount,
        ?AcademicNormConfig $config = null
    ): WorkloadBreakdown {
        if ($streamCount < 1) {
            throw new InvalidCurriculumNormException("Stream count must be at least 1. Given: {$streamCount}");
        }
        if ($groupCount < 1) {
            throw new InvalidCurriculumNormException("Group count must be at least 1. Given: {$groupCount}");
        }
        if ($subgroupCount < $groupCount) {
            throw new InvalidCurriculumNormException("Subgroup count ({$subgroupCount}) cannot be less than group count ({$groupCount}).");
        }
        if ($studentCount < 1) {
            throw new InvalidCurriculumNormException("Student count must be at least 1. Given: {$studentCount}");
        }

        $cfg = $config ?? AcademicNormConfig::createDefault();
        $prec = $cfg->getRoundingPrecision();

        // 1. Auditorium Hours
        // Lecture hours scale with number of pooled streams
        $lectureHours = round($subject->getLectureHours() * $streamCount, $prec);

        // Practical hours scale with number of academic groups
        $practicalHours = round($subject->getPracticalHours() * $groupCount, $prec);

        // Laboratory hours scale with laboratory subgroups
        $labHours = round($subject->getLabHours() * $subgroupCount, $prec);

        // Seminar hours scale with number of academic groups
        $seminarHours = round($subject->getSeminarHours() * $groupCount, $prec);

        // 2. Non-Auditorium Hours
        // Enterprise practice scales with groups
        $enterpriseHours = round($subject->getEnterprisePracticeHours() * $groupCount, $prec);

        // Coursework scales with total enrolled students
        $courseworkHours = round($subject->getCourseworkHoursPerStudent() * $studentCount, $prec);

        // Thesis guidance hours (Bachelor: 15h, Master: 25h by statutory norm)
        $thesisHours = round(
            ($subject->getBachelorThesisStudents() * $cfg->getBachelorThesisNorm()) +
            ($subject->getMasterThesisStudents() * $cfg->getMasterThesisNorm()),
            $prec
        );

        // Fixed exam and internship hours
        $examHours = round($subject->getExamHours(), $prec);
        $internshipHours = round($subject->getInternshipHours(), $prec);

        // Doctoral research supervision (50h per candidate)
        $doctoralHours = round($subject->getPhdStudents() * $cfg->getPhdSupervisionNorm(), $prec);

        return new WorkloadBreakdown(
            $subject->getCode(),
            $subject->getName(),
            $lectureHours,
            $practicalHours,
            $labHours,
            $seminarHours,
            $enterpriseHours,
            $courseworkHours,
            $thesisHours,
            $examHours,
            $internshipHours,
            $doctoralHours
        );
    }
}
