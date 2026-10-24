<?php

declare(strict_types=1);

namespace Afrt\Core\DTO;

/**
 * Data transfer object encapsulating the partition of academic cohorts into lecture streams and subgroups.
 */
final class StreamPartition
{
    /**
     * @var array<int, array{streamIndex: int, groups: string[], studentCount: int}>
     */
    private array $streams;

    /**
     * @var array<string, string[]> Mapping from group name to list of subgroup designators (e.g., ["EC-101" => ["EC-101-A", "EC-101-B"]])
     */
    private array $subgroupMap;

    private int $totalStudents;
    private int $streamCount;
    private int $groupCount;
    private int $subgroupCount;

    /**
     * @param array<int, array{streamIndex: int, groups: string[], studentCount: int}> $streams
     * @param array<string, string[]> $subgroupMap
     */
    public function __construct(
        array $streams,
        array $subgroupMap,
        int $totalStudents,
        int $streamCount,
        int $groupCount,
        int $subgroupCount
    ) {
        $this->streams = $streams;
        $this->subgroupMap = $subgroupMap;
        $this->totalStudents = $totalStudents;
        $this->streamCount = $streamCount;
        $this->groupCount = $groupCount;
        $this->subgroupCount = $subgroupCount;
    }

    /**
     * @return array<int, array{streamIndex: int, groups: string[], studentCount: int}>
     */
    public function getStreams(): array
    {
        return $this->streams;
    }

    /**
     * @return array<string, string[]>
     */
    public function getSubgroupMap(): array
    {
        return $this->subgroupMap;
    }

    public function getTotalStudents(): int
    {
        return $this->totalStudents;
    }

    public function getStreamCount(): int
    {
        return $this->streamCount;
    }

    public function getGroupCount(): int
    {
        return $this->groupCount;
    }

    public function getSubgroupCount(): int
    {
        return $this->subgroupCount;
    }

    /**
     * @return string[] Flat list of all subgroup names across all groups
     */
    public function getAllSubgroupNames(): array
    {
        $all = [];
        foreach ($this->subgroupMap as $subgroups) {
            foreach ($subgroups as $sg) {
                $all[] = $sg;
            }
        }
        return $all;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'streams' => $this->streams,
            'subgroupMap' => $this->subgroupMap,
            'totalStudents' => $this->totalStudents,
            'streamCount' => $this->streamCount,
            'groupCount' => $this->groupCount,
            'subgroupCount' => $this->subgroupCount,
        ];
    }
}
