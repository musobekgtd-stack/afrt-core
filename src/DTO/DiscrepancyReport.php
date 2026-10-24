<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

/**
 * Data transfer object encapsulating aggregated curriculum discrepancy analysis.
 */
final class DiscrepancyReport
{
    /** @var DiscrepancyItem[] */
    private array $items;
    private int $totalCurriculumTitles;
    private int $totalExternalTitles;
    private int $exactMatches;
    private int $normalizedMatches;
    private int $subpartMatches;
    private int $fuzzyMatches;
    private int $unmatchedTitles;
    private float $matchRate;

    /**
     * @param DiscrepancyItem[] $items
     */
    public function __construct(
        array $items,
        int $totalCurriculumTitles,
        int $totalExternalTitles
    ) {
        $this->items = $items;
        $this->totalCurriculumTitles = $totalCurriculumTitles;
        $this->totalExternalTitles = $totalExternalTitles;

        $exact = 0;
        $normalized = 0;
        $subpart = 0;
        $fuzzy = 0;
        $unmatched = 0;

        foreach ($items as $item) {
            switch ($item->getMatchType()) {
                case DiscrepancyItem::TYPE_EXACT:
                    $exact++;
                    break;
                case DiscrepancyItem::TYPE_NORMALIZED:
                    $normalized++;
                    break;
                case DiscrepancyItem::TYPE_SUBPART:
                    $subpart++;
                    break;
                case DiscrepancyItem::TYPE_FUZZY:
                    $fuzzy++;
                    break;
                default:
                    $unmatched++;
                    break;
            }
        }

        $this->exactMatches = $exact;
        $this->normalizedMatches = $normalized;
        $this->subpartMatches = $subpart;
        $this->fuzzyMatches = $fuzzy;
        $this->unmatchedTitles = $unmatched;

        $totalMatched = $exact + $normalized + $subpart + $fuzzy;
        $this->matchRate = $totalCurriculumTitles > 0
            ? round(($totalMatched / $totalCurriculumTitles) * 100.0, 2)
            : 0.0;
    }

    /**
     * @return DiscrepancyItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalCurriculumTitles(): int
    {
        return $this->totalCurriculumTitles;
    }

    public function getTotalExternalTitles(): int
    {
        return $this->totalExternalTitles;
    }

    public function getExactMatches(): int
    {
        return $this->exactMatches;
    }

    public function getNormalizedMatches(): int
    {
        return $this->normalizedMatches;
    }

    public function getSubpartMatches(): int
    {
        return $this->subpartMatches;
    }

    public function getFuzzyMatches(): int
    {
        return $this->fuzzyMatches;
    }

    public function getUnmatchedTitles(): int
    {
        return $this->unmatchedTitles;
    }

    public function getMatchRate(): float
    {
        return $this->matchRate;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return [
            'totalCurriculumTitles' => $this->totalCurriculumTitles,
            'totalExternalTitles' => $this->totalExternalTitles,
            'exactMatches' => $this->exactMatches,
            'normalizedMatches' => $this->normalizedMatches,
            'subpartMatches' => $this->subpartMatches,
            'fuzzyMatches' => $this->fuzzyMatches,
            'unmatchedTitles' => $this->unmatchedTitles,
            'matchRate' => $this->matchRate,
        ];
    }
}
