<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

/**
 * Data transfer object representing the comparison result for a single subject title.
 */
final class DiscrepancyItem
{
    public const TYPE_EXACT = 'EXACT';
    public const TYPE_NORMALIZED = 'NORMALIZED';
    public const TYPE_SUBPART = 'SUBPART';
    public const TYPE_FUZZY = 'FUZZY';
    public const TYPE_UNMATCHED = 'UNMATCHED';

    private string $curriculumTitle;
    private ?string $matchedExternalTitle;
    private string $matchType;
    private float $similarityScore;
    private string $normalizedCurriculum;
    private ?string $normalizedExternal;
    private string $notes;

    public function __construct(
        string $curriculumTitle,
        ?string $matchedExternalTitle,
        string $matchType,
        float $similarityScore,
        string $normalizedCurriculum,
        ?string $normalizedExternal = null,
        string $notes = ''
    ) {
        $this->curriculumTitle = $curriculumTitle;
        $this->matchedExternalTitle = $matchedExternalTitle;
        $this->matchType = $matchType;
        $this->similarityScore = round($similarityScore, 4);
        $this->normalizedCurriculum = $normalizedCurriculum;
        $this->normalizedExternal = $normalizedExternal;
        $this->notes = $notes;
    }

    public function getCurriculumTitle(): string
    {
        return $this->curriculumTitle;
    }

    public function getMatchedExternalTitle(): ?string
    {
        return $this->matchedExternalTitle;
    }

    public function getMatchType(): string
    {
        return $this->matchType;
    }

    public function getSimilarityScore(): float
    {
        return $this->similarityScore;
    }

    public function getNormalizedCurriculum(): string
    {
        return $this->normalizedCurriculum;
    }

    public function getNormalizedExternal(): ?string
    {
        return $this->normalizedExternal;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function isMatched(): bool
    {
        return $this->matchType !== self::TYPE_UNMATCHED;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'curriculumTitle' => $this->curriculumTitle,
            'matchedExternalTitle' => $this->matchedExternalTitle,
            'matchType' => $this->matchType,
            'similarityScore' => $this->similarityScore,
            'normalizedCurriculum' => $this->normalizedCurriculum,
            'normalizedExternal' => $this->normalizedExternal,
            'notes' => $this->notes,
        ];
    }
}
