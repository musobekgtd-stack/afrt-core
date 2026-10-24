<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Fixtures;

use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\DTO\FacultyMember;
use Afrt\Core\ValueObjects\CohortGroup;

final class SyntheticDepartmentFactory
{
    /**
     * @return CurriculumSubject[]
     */
    public static function createCurriculum(): array
    {
        return [
            new CurriculumSubject(
                'SYN-CS101',
                'Introduction to Computer Systems',
                lectureHours: 30.0,
                practicalHours: 30.0,
                labHours: 30.0,
                courseworkHoursPerStudent: 2.0,
                bachelorThesisStudents: 4,
                examHours: 4.0,
                educationLevel: 'bachelor'
            ),
            new CurriculumSubject(
                'SYN-EC201',
                'Macroeconomic Principles',
                lectureHours: 36.0,
                practicalHours: 36.0,
                seminarHours: 18.0,
                enterprisePracticeHours: 10.0,
                examHours: 4.0,
                educationLevel: 'bachelor'
            ),
            new CurriculumSubject(
                'SYN-DATA501',
                'Advanced Algorithmic Data Engineering',
                lectureHours: 24.0,
                labHours: 36.0,
                seminarHours: 12.0,
                masterThesisStudents: 3,
                phdStudents: 1,
                examHours: 4.0,
                educationLevel: 'master'
            ),
        ];
    }

    /**
     * @return CohortGroup[]
     */
    public static function createCohorts(): array
    {
        return [
            new CohortGroup('G1', 'SYN-CS-23-01', 26, 'uz', 1, 'bachelor'),
            new CohortGroup('G2', 'SYN-CS-23-02', 24, 'uz', 1, 'bachelor'),
            new CohortGroup('G3', 'SYN-CS-23-03', 28, 'uz', 1, 'bachelor'),
            new CohortGroup('G4', 'SYN-CS-23-04', 18, 'uz', 1, 'bachelor'),
            new CohortGroup('G5', 'SYN-EC-23-EN', 22, 'en', 1, 'bachelor'),
            new CohortGroup('G6', 'SYN-M-DATA-01', 14, 'uz', 1, 'master'),
        ];
    }

    /**
     * @return FacultyMember[]
     */
    public static function createFaculty(): array
    {
        return [
            new FacultyMember('FAC-001', 'Prof. Al-Khwarizmi Synthetic', 'professor', 1.0, 300.0, 150.0),
            new FacultyMember('FAC-002', 'Dr. Ada Lovelace Synthetic', 'docent', 1.0, 680.0, 350.0),
            new FacultyMember('FAC-003', 'Alan Turing Synthetic', 'senior_lecturer', 0.5, 120.0, 80.0),
            new FacultyMember('FAC-004', 'Claude Shannon Synthetic', 'assistant', 1.5, 800.0, 450.0),
        ];
    }
}
