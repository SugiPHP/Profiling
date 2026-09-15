<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * Creates Jobs and collects a JobMeasurement each time one finishes.
 */
class Profiler implements ProfilerInterface, JobFinishListener
{
    /**
     * @var JobMeasurement[]
     */
    private array $measurements = [];

    private int $jobCounter = 0;

    /**
     * @param string               $name    Human-readable name of the job.
     * @param array<string, mixed> $context Arbitrary contextual data.
     */
    public function start(string $name, array $context = []): Job
    {
        $id = ++$this->jobCounter . '-' . bin2hex(random_bytes(8));

        return new Job(
            name: $name,
            context: $context,
            id: $id,
            listener: $this
        );
    }

    /**
     * Called automatically by any Job this profiler started, when it finishes.
     */
    public function onJobFinish(JobMeasurement $measurement): void
    {
        $this->measurements[] = $measurement;
    }

    /**
     * @return JobMeasurement[]
     */
    public function getMeasurements(): array
    {
        return $this->measurements;
    }

    public function getTotalDuration(?string $name = null): float
    {
        return array_sum(array_map(
            static fn (JobMeasurement $m) => $m->duration,
            $this->filterByName($name)
        ));
    }

    public function getTotalMemoryUsage(?string $name = null): int
    {
        return array_sum(array_map(
            static fn (JobMeasurement $m) => $m->memoryUsage,
            $this->filterByName($name)
        ));
    }

    public function getCount(?string $name = null): int
    {
        return count($this->filterByName($name));
    }

    public function reset(): void
    {
        $this->measurements = [];
    }

    /**
     * @return JobMeasurement[]
     */
    private function filterByName(?string $name): array
    {
        if ($name === null) {
            return $this->measurements;
        }
        return array_filter($this->measurements, static fn (JobMeasurement $m) => $m->name === $name);
    }
}
