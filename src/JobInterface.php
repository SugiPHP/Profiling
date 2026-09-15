<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * A single measured unit of work, started by a ProfilerInterface.
 */
interface JobInterface
{
    /**
     * Finishes the job and returns its measurement.
     *
     * @param array<string, mixed> $context Additional contextual data to merge
     *                                      with the context supplied at start.
     */
    public function finish(array $context = []): JobMeasurement;

    /**
     * Whether finish() has already been called on this job.
     */
    public function isFinished(): bool;
}
