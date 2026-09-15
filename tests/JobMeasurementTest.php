<?php

declare(strict_types=1);

namespace SugiPHP\Profiling\Tests;

use SugiPHP\Profiling\JobMeasurement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JobMeasurement::class)]
class JobMeasurementTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $measurement = new JobMeasurement(
            id: 'job-1',
            name: 'render',
            context: ['foo' => 'bar'],
            startTime: 100.0,
            duration: 1.5,
            memoryUsage: 2048
        );

        $this->assertSame('job-1', $measurement->id);
        $this->assertSame('render', $measurement->name);
        $this->assertSame(['foo' => 'bar'], $measurement->context);
        $this->assertSame(100.0, $measurement->startTime);
        $this->assertSame(1.5, $measurement->duration);
        $this->assertSame(2048, $measurement->memoryUsage);
    }

    public function testContextCanBeEmpty(): void
    {
        $measurement = new JobMeasurement(
            id: 'job-2',
            name: 'render',
            context: [],
            startTime: 0.0,
            duration: 0.0,
            memoryUsage: 0
        );

        $this->assertSame([], $measurement->context);
    }

    public function testMemoryUsageCanBeNegative(): void
    {
        // A job that frees more memory than it allocates (e.g. triggers a GC run)
        // ends up with a negative delta; the class should not reject that.
        $measurement = new JobMeasurement(
            id: 'job-3',
            name: 'gc',
            context: [],
            startTime: 0.0,
            duration: 0.0,
            memoryUsage: -1024
        );

        $this->assertSame(-1024, $measurement->memoryUsage);
    }
}
