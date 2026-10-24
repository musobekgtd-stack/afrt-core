<?php

declare(strict_types=1);

namespace Afrt\Core\Tests\Unit;

use Afrt\Core\Exceptions\InvalidCohortConfigurationException;
use Afrt\Core\Services\CohortStreamBundler;
use Afrt\Core\ValueObjects\CohortGroup;
use PHPUnit\Framework\TestCase;

final class CohortStreamBundlerTest extends TestCase
{
    private CohortStreamBundler $bundler;

    protected function setUp(): void
    {
        $this->bundler = new CohortStreamBundler();
    }

    /**
     * BUND-001: Stream pooling under capacity limits
     * 4 groups of 25 students = 100 students total (within max 120 students and max 4 groups)
     */
    public function testStreamPoolingWithinCapacity(): void
    {
        $groups = [
            new CohortGroup('G1', 'CS-101', 25, 'uz', 1, 'bachelor'),
            new CohortGroup('G2', 'CS-102', 25, 'uz', 1, 'bachelor'),
            new CohortGroup('G3', 'CS-103', 25, 'uz', 1, 'bachelor'),
            new CohortGroup('G4', 'CS-104', 25, 'uz', 1, 'bachelor'),
        ];

        $partition = $this->bundler->bundleCohorts($groups, maxStreamSize: 120, maxGroupsPerStream: 4);

        $this->assertSame(1, $partition->getStreamCount());
        $this->assertSame(4, $partition->getGroupCount());
        $this->assertSame(100, $partition->getTotalStudents());
        $this->assertCount(4, $partition->getStreams()[0]['groups']);
    }

    /**
     * BUND-002: Subgroup division at student threshold (> 20)
     */
    public function testSubgroupDivisionAtThreshold(): void
    {
        $groups = [
            new CohortGroup('G1', 'CS-LARGE', 25, 'uz', 1, 'bachelor'), // > 20 -> 2 subgroups (-A, -B)
            new CohortGroup('G2', 'CS-SMALL', 18, 'uz', 1, 'bachelor'), // <= 20 -> 1 subgroup
        ];

        $partition = $this->bundler->bundleCohorts($groups, subgroupDivisionThreshold: 20);

        $subgroupMap = $partition->getSubgroupMap();
        $this->assertSame(['CS-LARGE-A', 'CS-LARGE-B'], $subgroupMap['CS-LARGE']);
        $this->assertSame(['CS-SMALL'], $subgroupMap['CS-SMALL']);
        $this->assertSame(3, $partition->getSubgroupCount());
    }

    /**
     * BUND-003: Incompatible cohort isolation (language and semester separation)
     */
    public function testIncompatibleCohortsAreIsolatedIntoSeparateStreams(): void
    {
        $groups = [
            new CohortGroup('G1', 'CS-UZ-1', 25, 'uz', 1, 'bachelor'),
            new CohortGroup('G2', 'CS-UZ-2', 25, 'uz', 1, 'bachelor'),
            new CohortGroup('G3', 'CS-EN-1', 25, 'en', 1, 'bachelor'), // English medium
            new CohortGroup('G4', 'CS-UZ-SEM3', 25, 'uz', 3, 'bachelor'), // Semester 3
        ];

        $partition = $this->bundler->bundleCohorts($groups);

        // Expecting 3 distinct streams: (uz, sem 1), (en, sem 1), (uz, sem 3)
        $this->assertSame(3, $partition->getStreamCount());
    }

    /**
     * BUND-004: Deterministic slicing and remainder allocation (Taqsimot)
     */
    public function testDeterministicTaqsimotGroupSlicing(): void
    {
        $groupNames = ['G1', 'G2', 'G3', 'G4', 'G5'];
        $allocations = [0 => 2, 1 => 3];

        $distributed = $this->bundler->distributeGroupsToAllocations($groupNames, $allocations);

        $this->assertSame(['G1', 'G2'], $distributed[0]);
        $this->assertSame(['G3', 'G4', 'G5'], $distributed[1]);
    }

    public function testTaqsimotModuloWrapAroundWhenAllocationExceedsGroups(): void
    {
        $groupNames = ['G1', 'G2'];
        $allocations = [0 => 2, 1 => 1]; // Total requested: 3, but only 2 exist -> wrap-around

        $distributed = $this->bundler->distributeGroupsToAllocations($groupNames, $allocations);

        $this->assertSame(['G1', 'G2'], $distributed[0]);
        $this->assertSame(['G1'], $distributed[1]);
    }

    /**
     * BUND-005: Duplicate cohort rejection
     */
    public function testDuplicateCohortIdThrowsException(): void
    {
        $this->expectException(InvalidCohortConfigurationException::class);

        $groups = [
            new CohortGroup('DUP-01', 'Group Alpha', 20),
            new CohortGroup('DUP-01', 'Group Beta', 22),
        ];

        $this->bundler->bundleCohorts($groups);
    }
}
