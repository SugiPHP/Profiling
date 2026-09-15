<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * Receives a JobMeasurement when a Job finishes.
 */
interface JobFinishListener
{
    public function onJobFinish(JobMeasurement $measurement): void;
}
