<?php

declare(strict_types=1);

namespace Afrt\Core\Services;

use Afrt\Core\Contracts\BundlerInterface;
use Afrt\Core\DTO\StreamPartition;
use Afrt\Core\Exceptions\InvalidCohortConfigurationException;
use Afrt\Core\ValueObjects\CohortGroup;

/**
 * Core computational engine for cohort stream bundling and subgroup partition.
 * Reproduces the pooling and slicing logic of AFRT Taqsimot.
 */
final class CohortStreamBundler implements BundlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function bundleCohorts(
        array $groups,
        int $maxStreamSize = 120,
        int $maxGroupsPerStream = 4,
        int $subgroupDivisionThreshold = 20
    ): StreamPartition {
        if (empty($groups)) {
            throw new InvalidCohortConfigurationException("Cannot bundle empty cohort group list.");
        }
        if ($maxStreamSize < 10) {
            throw new InvalidCohortConfigurationException("Max stream size must be at least 10 students.");
        }
        if ($maxGroupsPerStream < 1) {
            throw new InvalidCohortConfigurationException("Max groups per stream must be at least 1.");
        }

        /** @var array<string, CohortGroup[]> $cohortBuckets */
        $cohortBuckets = [];
        $totalStudents = 0;
        $seenGroupIds = [];

        foreach ($groups as $group) {
            if (!$group instanceof CohortGroup) {
                throw new InvalidCohortConfigurationException("All items must be instances of CohortGroup.");
            }

            if (isset($seenGroupIds[$group->getId()])) {
                throw new InvalidCohortConfigurationException("Duplicate cohort group ID detected: '{$group->getId()}'.");
            }
            $seenGroupIds[$group->getId()] = true;
            $totalStudents += $group->getStudentCount();

            // Bucket by compatible attributes: language + semester + education level
            $bucketKey = sprintf('%s_%d_%s', $group->getLanguage(), $group->getSemester(), $group->getEducationLevel());
            $cohortBuckets[$bucketKey][] = $group;
        }

        $streams = [];
        $subgroupMap = [];
        $streamIndexCounter = 1;
        $totalSubgroupCount = 0;

        foreach ($cohortBuckets as $bucketGroups) {
            $currentStreamGroups = [];
            $currentStreamStudents = 0;

            foreach ($bucketGroups as $group) {
                // Determine subgroup splitting for lab/practical
                if ($group->getStudentCount() > $subgroupDivisionThreshold) {
                    $subgroups = [
                        $group->getName() . '-A',
                        $group->getName() . '-B',
                    ];
                } else {
                    $subgroups = [$group->getName()];
                }
                $subgroupMap[$group->getName()] = $subgroups;
                $totalSubgroupCount += count($subgroups);

                // Check stream boundary constraints
                $wouldExceedStudents = ($currentStreamStudents + $group->getStudentCount()) > $maxStreamSize;
                $wouldExceedGroupCount = count($currentStreamGroups) >= $maxGroupsPerStream;

                if (!empty($currentStreamGroups) && ($wouldExceedStudents || $wouldExceedGroupCount)) {
                    // Close current stream
                    $streams[] = [
                        'streamIndex' => $streamIndexCounter++,
                        'groups' => $currentStreamGroups,
                        'studentCount' => $currentStreamStudents,
                    ];
                    $currentStreamGroups = [];
                    $currentStreamStudents = 0;
                }

                $currentStreamGroups[] = $group->getName();
                $currentStreamStudents += $group->getStudentCount();
            }

            // Flush remaining stream in bucket
            if (!empty($currentStreamGroups)) {
                $streams[] = [
                    'streamIndex' => $streamIndexCounter++,
                    'groups' => $currentStreamGroups,
                    'studentCount' => $currentStreamStudents,
                ];
            }
        }

        return new StreamPartition(
            $streams,
            $subgroupMap,
            $totalStudents,
            count($streams),
            count($groups),
            $totalSubgroupCount
        );
    }

    /**
     * {@inheritdoc}
     */
    public function distributeGroupsToAllocations(
        array $groupNames,
        array $allocationCounts,
        string $activityType = 'practical'
    ): array {
        if (empty($groupNames) || empty($allocationCounts)) {
            return [];
        }

        $assignments = [];
        $currentIndex = 0;
        $totalGroups = count($groupNames);

        foreach ($allocationCounts as $teacherIndex => $count) {
            if ($count <= 0) {
                $assignments[$teacherIndex] = [];
                continue;
            }

            $teacherGroups = [];
            for ($i = 0; $i < $count; $i++) {
                if ($currentIndex < $totalGroups) {
                    $teacherGroups[] = $groupNames[$currentIndex++];
                } else {
                    // Wrap-around modulo index allocation if allocated count exceeds distinct groups
                    $wrappedIndex = ($currentIndex++) % $totalGroups;
                    $teacherGroups[] = $groupNames[$wrappedIndex];
                }
            }
            $assignments[$teacherIndex] = $teacherGroups;
        }

        return $assignments;
    }
}
