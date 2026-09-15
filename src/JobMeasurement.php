<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * Immutable snapshot of a finished Job: its identity, contextual data,
 * elapsed time, and memory usage.
 */
class JobMeasurement
{
    /**
     * @param string               $id         Unique identifier of the job.
     * @param string               $name       Human-readable name of the job.
     * @param array<string, mixed> $context    Contextual data collected during the job.
     * @param float                $startTime  Start time of the job.
     * @param float                $duration   Elapsed time, in seconds.
     * @param int                  $memoryUsage Change in memory usage, in bytes.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $context,
        public readonly float $startTime,
        public readonly float $duration,
        public readonly int $memoryUsage    // bytes
    ) {
    }
}
