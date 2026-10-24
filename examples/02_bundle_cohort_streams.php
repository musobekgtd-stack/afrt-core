<?php

declare(strict_types=1);

/**
 * AFRT Core - Example 02: Cohort Stream Bundling & Subgroup Division
 * Demonstrates pooling academic groups into lecture streams and dividing laboratory subgroups.
 */

require_once __DIR__ . '/../src/autoload.php';

use Afrt\Core\Services\CohortStreamBundler;
use Afrt\Core\ValueObjects\CohortGroup;

echo "=== AFRT Core: Cohort Stream Bundling Example ===\n\n";

$bundler = new CohortStreamBundler();

$groups = [
    new CohortGroup('G1', 'SYN-CS-23-01', 26, 'uz', 1, 'bachelor'),
    new CohortGroup('G2', 'SYN-CS-23-02', 24, 'uz', 1, 'bachelor'),
    new CohortGroup('G3', 'SYN-CS-23-03', 28, 'uz', 1, 'bachelor'),
    new CohortGroup('G4', 'SYN-CS-23-04', 18, 'uz', 1, 'bachelor'),
    new CohortGroup('G5', 'SYN-CS-23-05', 30, 'uz', 1, 'bachelor'),
    new CohortGroup('G6', 'SYN-EC-23-EN', 22, 'en', 1, 'bachelor'), // Different language: separate stream
];

// Bundle with max 100 students per stream and max 4 groups per stream
$partition = $bundler->bundleCohorts($groups, maxStreamSize: 100, maxGroupsPerStream: 4, subgroupDivisionThreshold: 20);

echo "Total Cohort Groups:   {$partition->getGroupCount()}\n";
echo "Total Students:        {$partition->getTotalStudents()}\n";
echo "Lecture Streams Formed: {$partition->getStreamCount()}\n";
echo "Subgroups Generated:   {$partition->getSubgroupCount()}\n\n";

echo "--- Formed Streams ---\n";
foreach ($partition->getStreams() as $stream) {
    echo "Stream #{$stream['streamIndex']}: " . implode(', ', $stream['groups']) . " ({$stream['studentCount']} students)\n";
}

echo "\n--- Subgroup Division (threshold > 20) ---\n";
foreach ($partition->getSubgroupMap() as $groupName => $subgroups) {
    echo "{$groupName}: " . implode(', ', $subgroups) . "\n";
}

// Demonstrate Taqsimot group distribution across teachers
echo "\n--- Teacher Group Distribution (Taqsimot Slicing) ---\n";
$allGroupNames = ['SYN-CS-23-01', 'SYN-CS-23-02', 'SYN-CS-23-03', 'SYN-CS-23-04', 'SYN-CS-23-05'];
$teacherAllocations = [0 => 2, 1 => 3]; // Teacher 0 gets 2 groups, Teacher 1 gets 3 groups
$distributed = $bundler->distributeGroupsToAllocations($allGroupNames, $teacherAllocations);

foreach ($distributed as $teacherIndex => $assigned) {
    echo "Teacher {$teacherIndex}: " . implode(', ', $assigned) . "\n";
}

echo "\n[SUCCESS] Cohort partitioning executed deterministically.\n";
