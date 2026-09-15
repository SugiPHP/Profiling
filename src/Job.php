<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

use LogicException;
use Throwable;

/**
 * Represents a single measured unit of work.
 *
 * Captures elapsed time and memory usage between construction and a call
 * to finish().
 */
class Job implements JobInterface
{
    public readonly string $id;
    private array $context;
    public readonly int $startMemory;
    public readonly float $startTime;
    private readonly int $startTimeMonotonic;
    private bool $isFinished = false;

    /**
     * @param string                 $name     Human-readable name of the job.
     * @param array<string, mixed>   $context  Arbitrary contextual data.
     * @param string|null            $id       Identifier for the job.
     * @param JobFinishListener|null $listener Optional listener notified when finish() completes.
     */
    public function __construct(
        public readonly string $name,
        array $context = [],
        ?string $id = null,
        private readonly ?JobFinishListener $listener = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(8));
        $this->context = $context;
        $this->startMemory = memory_get_usage();
        $this->startTime = microtime(true);
        $this->startTimeMonotonic = hrtime(true);
    }

    public function __destruct()
    {
        if (!$this->isFinished) {
            trigger_error(
                sprintf('Job "%s" (id: %s) was never finished.', $this->name, $this->id),
                E_USER_WARNING
            );
        }
    }

    /**
     * @param array<string, mixed> $context Additional contextual data to merge
     *                                      with the context supplied at construction.
     *
     * @throws LogicException              If the job has already been finished.
     * @throws JobFinishListenerException  If the listener's onJobFinish() throws;
     *                                      the computed JobMeasurement can still be
     *                                      retrieved via getMeasurement() on the exception.
     */
    public function finish(array $context = []): JobMeasurement
    {
        if ($this->isFinished) {
            throw new LogicException(
                sprintf('Job "%s" (id: %s) has already been finished.', $this->name, $this->id)
            );
        }

        $this->context = array_replace($this->context, $context);

        $measurement = new JobMeasurement(
            id: $this->id,
            name: $this->name,
            context: $this->context,
            startTime: $this->startTime,
            duration: (hrtime(true) - $this->startTimeMonotonic) / 1e9,
            memoryUsage: memory_get_usage() - $this->startMemory,
        );

        $this->isFinished = true;

        try {
            $this->listener?->onJobFinish($measurement);
        } catch (Throwable $e) {
            throw new JobFinishListenerException($measurement, $e);
        }

        return $measurement;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }
}
