<?php

declare(strict_types=1);

namespace Afrt\Core\Contracts;

use Afrt\Core\DTO\StreamPartition;
use Afrt\Core\ValueObjects\CohortGroup;

interface BundlerInterface
{
    /**
     * Bundle cohort groups into lecture streams and generate subgroup divisions.
     *
     * @param CohortGroup[] $groups Array of student academic groups
     * @param int $maxStreamSize Maximum number of students per lecture stream
     * @param int $maxGroupsPerStream Maximum number of academic groups allowed in one lecture stream
     * @param int $subgroupDivisionThreshold Student threshold above which a group is split into laboratory subgroups (e.g., 20)
     * @return StreamPartition Partitioned lecture streams and subgroup assignments
     */
    public function bundleCohorts(
        array $groups,
        int $maxStreamSize = 120,
        int $maxGroupsPerStream = 4,
        int $subgroupDivisionThreshold = 20
    ): StreamPartition;

    /**
     * Split a list of group names across multiple teachers according to allocation counts.
     * Reproduces the exact slicing algorithm of production Taqsimot::calculateGuruhNomlari.
     *
     * @param string[] $groupNames Ordered list of academic group names
     * @param int[] $allocationCounts Number of groups assigned to each teacher/slice
     * @param string $activityType Type of activity ('lecture', 'practical', 'lab', etc.)
     * @return array<int, string[]> Array of group names allocated to each teacher index
     */
    public function distributeGroupsToAllocations(
        array $groupNames,
        array $allocationCounts,
        string $activityType = 'practical'
    ): array;
}
