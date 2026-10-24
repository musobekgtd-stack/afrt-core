<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Unit;

use Afrt\Core\DTO\DiscrepancyItem;
use Afrt\Core\Services\SubjectDiscrepancyMatcher;
use PHPUnit\Framework\TestCase;

final class SubjectDiscrepancyMatcherTest extends TestCase
{
    private SubjectDiscrepancyMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new SubjectDiscrepancyMatcher();
    }

    /**
     * MATCH-001: Exact match identification
     */
    public function testExactTitleMatch(): void
    {
        $report = $this->matcher->reconcileTitles(
            ['Algoritmik tillar'],
            ['Algoritmik tillar']
        );

        $this->assertSame(1, $report->getExactMatches());
        $this->assertSame(100.0, $report->getMatchRate());
        $item = $report->getItems()[0];
        $this->assertSame(DiscrepancyItem::TYPE_EXACT, $item->getMatchType());
        $this->assertSame(1.0, $item->getSimilarityScore());
    }

    /**
     * MATCH-002: Normalized match with Cyrillic-to-Latin homoglyphs
     */
    public function testCyrillicToLatinNormalizedMatch(): void
    {
        $report = $this->matcher->reconcileTitles(
            ['O‘zbekiston tarixi'],
            ['Ўзбекистон тарихи']
        );

        $this->assertSame(1, $report->getNormalizedMatches());
        $item = $report->getItems()[0];
        $this->assertSame(DiscrepancyItem::TYPE_NORMALIZED, $item->getMatchType());
        $this->assertTrue($item->isMatched());
    }

    /**
     * MATCH-003: Subpart designator stripping (1-qism, 2-modul)
     */
    public function testSubpartPartitionedTitleMatch(): void
    {
        $report = $this->matcher->reconcileTitles(
            ['Mikroiqtisodiyot', 'Oliy matematika'],
            ['Mikroiqtisodiyot (1-qism)', 'Oliy matematika 2-modul']
        );

        $this->assertSame(2, $report->getSubpartMatches());
        foreach ($report->getItems() as $item) {
            $this->assertSame(DiscrepancyItem::TYPE_SUBPART, $item->getMatchType());
            $this->assertTrue($item->isMatched());
        }
    }

    /**
     * MATCH-004: Fuzzy match with minor spelling variation
     */
    public function testFuzzySimilarityMatch(): void
    {
        $report = $this->matcher->reconcileTitles(
            ['Ekonometrik modellashtirish'],
            ['Ekonometrika modellashtirish']
        );

        $this->assertSame(1, $report->getFuzzyMatches());
        $item = $report->getItems()[0];
        $this->assertSame(DiscrepancyItem::TYPE_FUZZY, $item->getMatchType());
        $this->assertGreaterThanOrEqual(0.80, $item->getSimilarityScore());
    }

    /**
     * MATCH-005: Unmatched detection with no false positive
     */
    public function testUnmatchedDetection(): void
    {
        $report = $this->matcher->reconcileTitles(
            ['Kvant hisoblash nazariyasi'],
            ['Iqtisodiy tahlil', 'Buxgalteriya hisobi']
        );

        $this->assertSame(1, $report->getUnmatchedTitles());
        $this->assertSame(0.0, $report->getMatchRate());
        $item = $report->getItems()[0];
        $this->assertSame(DiscrepancyItem::TYPE_UNMATCHED, $item->getMatchType());
        $this->assertNull($item->getMatchedExternalTitle());
        $this->assertFalse($item->isMatched());
    }
}
