<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

use RuntimeException;
use Throwable;

/**
 * Thrown when a JobFinishListener::onJobFinish() call fails.
 *
 * Carries the JobMeasurement that was already computed, so callers can
 * still retrieve it despite the listener failure.
 */
class JobFinishListenerException extends RuntimeException
{
    public function __construct(
        private readonly JobMeasurement $measurement,
        Throwable $previous
    ) {
        parent::__construct(
            sprintf('Listener failed to handle job "%s" (id: %s): %s', $measurement->name, $measurement->id, $previous->getMessage()),
            0,
            $previous
        );
    }

    public function getMeasurement(): JobMeasurement
    {
        return $this->measurement;
    }
}
