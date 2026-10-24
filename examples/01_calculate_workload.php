<?php

declare(strict_types=1);

/**
 * AFRT Core - Example 01: Academic Workload Calculation
 * Demonstrates computing workload for undergraduate curriculum with stream bundling and subgroups.
 */

require_once __DIR__ . '/../src/autoload.php';

use Afrt\Core\DTO\CurriculumSubject;
use Afrt\Core\Services\WorkloadCalculator;
use Afrt\Core\ValueObjects\AcademicNormConfig;

echo "=== AFRT Core: Academic Workload Calculation Example ===\n\n";

$calculator = new WorkloadCalculator();
$config = AcademicNormConfig::createDefault();

// 1. Define synthetic curriculum subject
$subject = new CurriculumSubject(
    'SYN-CS101',
    'Introduction to Computer Systems',
    lectureHours: 36.0,
    practicalHours: 36.0,
    labHours: 36.0,
    seminarHours: 0.0,
    enterprisePracticeHours: 0.0,
    courseworkHoursPerStudent: 2.0,
    bachelorThesisStudents: 5,
    examHours: 6.0,
    educationLevel: 'bachelor'
);

// 2. Cohort configuration: 4 groups of 25 students = 100 students total
// Bundled into 1 lecture stream, 4 practical groups, 8 lab subgroups (each > 20 students splits into 2)
$streamCount = 1;
$groupCount = 4;
$subgroupCount = 8;
$studentCount = 100;

$breakdown = $calculator->calculateSubjectWorkload(
    $subject,
    $streamCount,
    $groupCount,
    $subgroupCount,
    $studentCount,
    $config
);

echo "Subject: {$breakdown->getSubjectCode()} - {$breakdown->getSubjectName()}\n";
echo "Lecture Hours (1 stream x 36h):        {$breakdown->getLectureHours()} h\n";
echo "Practical Hours (4 groups x 36h):      {$breakdown->getPracticalHours()} h\n";
echo "Lab Hours (8 subgroups x 36h):         {$breakdown->getLabHours()} h\n";
echo "Coursework (100 students x 2h):        {$breakdown->getCourseworkHours()} h\n";
echo "Thesis Guidance (5 bach x 15h norm):   {$breakdown->getThesisHours()} h\n";
echo "Examination Hours:                     {$breakdown->getExamHours()} h\n";
echo "--------------------------------------------------------\n";
echo "Total Auditorium Hours:                {$breakdown->getAuditoriumHours()} h\n";
echo "Total Non-Auditorium Hours:            {$breakdown->getNonAuditoriumHours()} h\n";
echo "GRAND TOTAL WORKLOAD:                  {$breakdown->getTotalHours()} h\n\n";

echo "[SUCCESS] Calculation completed deterministically.\n";
