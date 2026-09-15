<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * Starts Jobs. Implementations may record their measurements (Profiler)
 * or discard them entirely (NullProfiler).
 */
interface ProfilerInterface
{
    /**
     * @param string               $name    Human-readable name of the job.
     * @param array<string, mixed> $context Arbitrary contextual data.
     */
    public function start(string $name, array $context = []): JobInterface;
}
