<?php

declare(strict_types=1);

/**
 * AFRT Core - Example 04: Course Title Discrepancy Matching
 * Demonstrates cross-system reconciliation between curriculum plans and timetable/HEMIS registries.
 */

require_once __DIR__ . '/../src/autoload.php';

use Afrt\Core\Services\SubjectDiscrepancyMatcher;

echo "=== AFRT Core: Subject Discrepancy Reconciliation Example ===\n\n";

$matcher = new SubjectDiscrepancyMatcher();

$curriculumTitles = [
    'Algoritmik tillar va dasturlash',
    'O‘zbekiston tarixi',
    'Ma\'lumotlar bazasi',
    'Mikroiqtisodiyot',
    'Oliy matematika',
    'Ekonometrik modellashtirish',
    'Kvant hisoblash asoslari',
];

$externalTitles = [
    'Algoritmik tillar va dasturlash',
    'Ўзбекистон тарихи',
    'Ma‘lumotlar bazasi',
    'Mikroiqtisodiyot (1-qism)',
    'Oliy matematika 2-modul',
    'Ekonometrika modellashtirish',
    'Sun\'iy intellekt asoslari',
];

$report = $matcher->reconcileTitles($curriculumTitles, $externalTitles, similarityThreshold: 0.80);

echo "Total Curriculum Subjects: {$report->getTotalCurriculumTitles()}\n";
echo "Total External Subjects:   {$report->getTotalExternalTitles()}\n";
echo "Overall Match Rate:        {$report->getMatchRate()}%\n";
echo "Exact Matches:             {$report->getExactMatches()}\n";
echo "Normalized Matches:        {$report->getNormalizedMatches()}\n";
echo "Subpart Matches:           {$report->getSubpartMatches()}\n";
echo "Fuzzy Matches:             {$report->getFuzzyMatches()}\n";
echo "Unmatched:                 {$report->getUnmatchedTitles()}\n\n";

echo "--- Reconciliation Details ---\n";
printf("%-30s | %-12s | %-6s | %s\n", "Curriculum Title", "Match Type", "Score", "Matched External Title");
echo str_repeat('-', 85) . "\n";

foreach ($report->getItems() as $item) {
    printf(
        "%-30s | %-12s | %-6.2f | %s\n",
        mb_strimwidth($item->getCurriculumTitle(), 0, 30, '...'),
        $item->getMatchType(),
        $item->getSimilarityScore(),
        $item->getMatchedExternalTitle() ?? '(none)'
    );
}

echo "\n[SUCCESS] Discrepancy reconciliation executed successfully.\n";
