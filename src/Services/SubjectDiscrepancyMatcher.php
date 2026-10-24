<?php

declare(strict_types=1);

namespace Afrt\Core\Services;

use Afrt\Core\Contracts\MatcherInterface;
use Afrt\Core\DTO\DiscrepancyItem;
use Afrt\Core\DTO\DiscrepancyReport;

/**
 * Core computational engine for curriculum and timetable discrepancy reconciliation.
 * Standalone reproduction of AFRT HEMIS discrepancy matching and title normalization.
 */
final class SubjectDiscrepancyMatcher implements MatcherInterface
{
    /**
     * Map of common Cyrillic characters to Latin equivalents for cross-script reconciliation.
     * @var array<string, string>
     */
    private const HOMOGLYPH_MAP = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'x', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh',
        'ъ' => "'", 'ы' => 'i', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'ў' => "o'", 'қ' => 'q', 'ғ' => "g'", 'ҳ' => 'h',
    ];

    /**
     * {@inheritdoc}
     */
    public function reconcileTitles(
        array $curriculumTitles,
        array $externalTitles,
        float $similarityThreshold = 0.80
    ): DiscrepancyReport {
        $items = [];
        $unmatchedExternalPool = $externalTitles;

        // Precompute normalized and parsed forms of external titles
        $externalIndex = [];
        foreach ($externalTitles as $ext) {
            $norm = $this->normalizeTitle($ext);
            $parsed = $this->parseSubjectSubparts($ext);
            $externalIndex[] = [
                'raw' => $ext,
                'normalized' => $norm,
                'parsed' => $parsed,
            ];
        }

        foreach ($curriculumTitles as $curr) {
            $currNorm = $this->normalizeTitle($curr);
            $currParsed = $this->parseSubjectSubparts($curr);

            $matchedItem = null;

            // Step 1: Exact Match
            foreach ($externalIndex as $idx => $extData) {
                if ($curr === $extData['raw']) {
                    $matchedItem = new DiscrepancyItem(
                        $curr,
                        $extData['raw'],
                        DiscrepancyItem::TYPE_EXACT,
                        1.0,
                        $currNorm,
                        $extData['normalized'],
                        'Identical raw string match'
                    );
                    break;
                }
            }

            // Step 2: Normalized Match
            if ($matchedItem === null) {
                foreach ($externalIndex as $extData) {
                    if ($currNorm !== '' && $currNorm === $extData['normalized']) {
                        $matchedItem = new DiscrepancyItem(
                            $curr,
                            $extData['raw'],
                            DiscrepancyItem::TYPE_NORMALIZED,
                            0.98,
                            $currNorm,
                            $extData['normalized'],
                            'Normalized title match (homoglyphs and case stripped)'
                        );
                        break;
                    }
                }
            }

            // Step 3: Subpart-Aware Match
            if ($matchedItem === null && $currParsed['base'] !== '') {
                $currBaseNorm = $this->normalizeTitle($currParsed['base']);
                foreach ($externalIndex as $extData) {
                    $extBaseNorm = $this->normalizeTitle($extData['parsed']['base']);
                    if ($currBaseNorm !== '' && $currBaseNorm === $extBaseNorm) {
                        $matchedItem = new DiscrepancyItem(
                            $curr,
                            $extData['raw'],
                            DiscrepancyItem::TYPE_SUBPART,
                            0.90,
                            $currNorm,
                            $extData['normalized'],
                            sprintf(
                                'Subpart partitioned match: base "%s" (part %s vs %s)',
                                $currParsed['base'],
                                $currParsed['part'] ?? 'none',
                                $extData['parsed']['part'] ?? 'none'
                            )
                        );
                        break;
                    }
                }
            }

            // Step 4: Fuzzy Levenshtein / Similar-text Match
            if ($matchedItem === null) {
                $bestScore = 0.0;
                $bestExt = null;

                foreach ($externalIndex as $extData) {
                    $score = $this->calculateSimilarityScore($currNorm, $extData['normalized']);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestExt = $extData;
                    }
                }

                if ($bestExt !== null && $bestScore >= $similarityThreshold) {
                    $matchedItem = new DiscrepancyItem(
                        $curr,
                        $bestExt['raw'],
                        DiscrepancyItem::TYPE_FUZZY,
                        $bestScore,
                        $currNorm,
                        $bestExt['normalized'],
                        sprintf('Fuzzy similarity match (score: %.2f)', $bestScore)
                    );
                }
            }

            // Step 5: Unmatched
            if ($matchedItem === null) {
                $matchedItem = new DiscrepancyItem(
                    $curr,
                    null,
                    DiscrepancyItem::TYPE_UNMATCHED,
                    0.0,
                    $currNorm,
                    null,
                    'No counterpart identified within similarity threshold'
                );
            }

            $items[] = $matchedItem;
        }

        return new DiscrepancyReport(
            $items,
            count($curriculumTitles),
            count($externalTitles)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeTitle(string $rawTitle): string
    {
        $text = mb_strtolower(trim($rawTitle), 'UTF-8');

        // Normalize various apostrophe and quote characters
        $text = str_replace(["‘", "’", "`", "ʻ", "ʼ", "''"], "'", $text);

        // Transliterate Cyrillic characters to Latin homoglyphs
        $text = strtr($text, self::HOMOGLYPH_MAP);

        // Remove Roman numerals (e.g., I, II, III, IV, V)
        $text = preg_replace('/\b(i{1,3}|iv|v|vi{0,3}|ix|x)\b/u', '', $text);

        // Remove punctuation and special symbols except single apostrophe
        $text = preg_replace("/[^\p{L}\p{N}\s']/u", ' ', $text);

        // Collapse multiple whitespace
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * {@inheritdoc}
     */
    public function parseSubjectSubparts(string $title): array
    {
        $base = $title;
        $part = null;
        $semester = null;

        // Pattern 1: Suffix with part / qism / modul (e.g., "Mikroiqtisodiyot 1-qism", "Algebra (2-modul)")
        if (preg_match('/^(.*?)(?:\s*[-–(]?\s*(\d+)\s*[-_]?(?:qism|modul|bosqich|part)[)-]?|\s*[-–(]?\s*(?:qism|modul)\s*(\d+)[)-]?)(.*)$/iu', $title, $matches)) {
            $base = trim($matches[1] . ' ' . ($matches[4] ?? ''));
            $part = !empty($matches[2]) ? $matches[2] : ($matches[3] ?? null);
        }

        // Pattern 2: Semester designation (e.g., "5-semestr", "semestr 3")
        if (preg_match('/(?:\s*[-–(]?\s*(\d+)\s*[-_]?semestr|\s*[-–(]?\s*semestr\s*(\d+)[)-]?)/iu', $title, $semMatches)) {
            $semNum = !empty($semMatches[1]) ? $semMatches[1] : ($semMatches[2] ?? null);
            if ($semNum !== null) {
                $semester = (int) $semNum;
                $base = preg_replace('/(?:\s*[-–(]?\s*\d+\s*[-_]?semestr|\s*[-–(]?\s*semestr\s*\d+[)-]?)/iu', '', $base);
            }
        }

        // Pattern 3: Trailing digits indicating part (e.g., "Matematika 1", "Fizika 2")
        if ($part === null && preg_match('/^(.*?)\s+(\d+)$/u', trim($base), $digitMatches)) {
            $base = $digitMatches[1];
            $part = $digitMatches[2];
        }

        return [
            'base' => trim($base),
            'part' => $part,
            'semester' => $semester,
        ];
    }

    /**
     * Compute string similarity between 0.0 and 1.0 using Levenshtein distance & Similar Text.
     */
    private function calculateSimilarityScore(string $str1, string $str2): float
    {
        if ($str1 === '' || $str2 === '') {
            return 0.0;
        }
        if ($str1 === $str2) {
            return 1.0;
        }

        similar_text($str1, $str2, $percent);
        $similarTextScore = $percent / 100.0;

        $maxLen = max(mb_strlen($str1), mb_strlen($str2));
        $levDist = levenshtein(substr($str1, 0, 255), substr($str2, 0, 255));
        $levScore = 1.0 - ($levDist / max(1, $maxLen));
        $levScore = max(0.0, $levScore);

        // Weighted blend: 60% similar_text, 40% Levenshtein
        return round(($similarTextScore * 0.6) + ($levScore * 0.4), 4);
    }
}
