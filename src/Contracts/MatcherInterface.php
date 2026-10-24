<?php

declare(strict_types=1);

namespace Afrt\Core\Contracts;

use Afrt\Core\DTO\DiscrepancyReport;

interface MatcherInterface
{
    /**
     * Reconcile curriculum titles from local workload tables against timetable/HEMIS registry titles.
     * Identifies identical matches, normalized string matches, subpart-partitioned matches, and discrepancies.
     *
     * @param string[] $curriculumTitles List of course titles from academic curriculum
     * @param string[] $externalTitles List of course titles from timetable or external registry
     * @param float $similarityThreshold Threshold for fuzzy/Levenshtein matching (0.0 to 1.0, default 0.80)
     * @return DiscrepancyReport Reconciliation report detailing exact, normalized, fuzzy, and unmatched titles
     */
    public function reconcileTitles(
        array $curriculumTitles,
        array $externalTitles,
        float $similarityThreshold = 0.80
    ): DiscrepancyReport;

    /**
     * Normalize a subject title by removing roman numerals, part numbers, punctuation,
     * converting Cyrillic/Latin homoglyphs, and stripping whitespace.
     *
     * @param string $rawTitle Unnormalized subject name
     * @return string Canonical normalized title
     */
    public function normalizeTitle(string $rawTitle): string;

    /**
     * Parse a compound subject title into base title and subpart designators (e.g., "1-qism", "2-modul").
     *
     * @param string $title Subject title
     * @return array{base: string, part: ?string, semester: ?int}
     */
    public function parseSubjectSubparts(string $title): array;
}
