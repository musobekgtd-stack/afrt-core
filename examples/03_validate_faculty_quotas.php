<?php

declare(strict_types=1);

/**
 * AFRT Core - Example 03: Faculty Workload Quota Validation
 * Demonstrates validation of appointments against statutory quotas, saturation limits, and overload prevention.
 */

require_once __DIR__ . '/../src/autoload.php';

use Afrt\Core\DTO\AllocationRequest;
use Afrt\Core\DTO\FacultyMember;
use Afrt\Core\Services\WorkloadValidator;
use Afrt\Core\ValueObjects\AcademicNormConfig;

echo "=== AFRT Core: Faculty Quota Validation Example ===\n\n";

$validator = new WorkloadValidator();
$config = AcademicNormConfig::createDefault();

// 1. Faculty member with 1.0 FTE (Max capacity: 750.0h)
$faculty1 = new FacultyMember(
    id: 'FAC-SYN-001',
    name: 'Dr. Ada Lovelace Synthetic',
    academicRank: 'docent',
    fte: 1.0,
    currentAllocatedHours: 650.0,
    currentAuditoriumHours: 350.0
);

// 2. Scenario A: Valid allocation with saturation warning (650h + 80h = 730h / 750h = 97.3%)
$requestA = new AllocationRequest(
    subjectCode: 'SYN-CS101',
    subjectName: 'Computer Systems',
    activityType: 'practical',
    hours: 80.0,
    isAuditorium: true
);

$resultA = $validator->validateAllocation($faculty1, $requestA, $config);

echo "Scenario A (Valid with warning):\n";
echo "  Is Valid:             " . ($resultA->isValid() ? 'YES' : 'NO') . "\n";
echo "  Resulting Workload:   {$resultA->getResultingHours()} / {$resultA->getMaxPermittedHours()} h\n";
echo "  FTE Utilization:      {$resultA->getFteUtilizationPercent()}%\n";
echo "  Remaining Capacity:   {$resultA->getRemainingHours()} h\n";
foreach ($resultA->getWarnings() as $warning) {
    echo "  [WARNING] {$warning}\n";
}

// 3. Scenario B: Quota Exceeded (650h + 120h = 770h > 750h)
$requestB = new AllocationRequest(
    subjectCode: 'SYN-CS101',
    subjectName: 'Computer Systems',
    activityType: 'lecture',
    hours: 120.0,
    isAuditorium: true
);

$resultB = $validator->validateAllocation($faculty1, $requestB, $config);

echo "\nScenario B (Overload Violation):\n";
echo "  Is Valid:             " . ($resultB->isValid() ? 'YES' : 'NO') . "\n";
echo "  Resulting Workload:   {$resultB->getResultingHours()} / {$resultB->getMaxPermittedHours()} h\n";
foreach ($resultB->getViolations() as $violation) {
    echo "  [VIOLATION] {$violation}\n";
}

echo "\n[SUCCESS] Faculty capacity validation completed.\n";
