<?php

declare(strict_types=1);

namespace SugiPHP\Profiling\Tests;

use SugiPHP\Profiling\Job;
use SugiPHP\Profiling\JobMeasurement;
use SugiPHP\Profiling\Profiler;
use SugiPHP\Profiling\ProfilerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Profiler::class)]
#[UsesClass(Job::class)]
#[UsesClass(JobMeasurement::class)]
class ProfilerTest extends TestCase
{
    public function testImplementsProfilerInterface(): void
    {
        $this->assertInstanceOf(ProfilerInterface::class, new Profiler());
    }

    public function testStartAssignsUniqueIds(): void
    {
        $profiler = new Profiler();

        $a = $profiler->start('a');
        $b = $profiler->start('b');

        $this->assertNotSame('', $a->id);
        $this->assertNotSame($a->id, $b->id);

        $a->finish();
        $b->finish();
    }

    public function testGetMeasurementsIsEmptyInitially(): void
    {
        $profiler = new Profiler();

        $this->assertSame([], $profiler->getMeasurements());
    }

    public function testFinishingAJobCollectsItsMeasurement(): void
    {
        $profiler = new Profiler();
        $measurement = $profiler->start('db.query')->finish();

        $this->assertSame([$measurement], $profiler->getMeasurements());
    }

    public function testGetCountTotalsAndFiltersByName(): void
    {
        $profiler = new Profiler();
        $profiler->start('db.query')->finish();
        $profiler->start('db.query')->finish();
        $profiler->start('render')->finish();

        $this->assertSame(3, $profiler->getCount());
        $this->assertSame(2, $profiler->getCount('db.query'));
        $this->assertSame(1, $profiler->getCount('render'));
        $this->assertSame(0, $profiler->getCount('missing'));
    }

    public function testGetTotalDurationTotalsAndFiltersByName(): void
    {
        $profiler = new Profiler();

        $job = $profiler->start('db.query');
        usleep(1000);
        $job->finish();

        $profiler->start('render')->finish();

        $this->assertGreaterThan(0, $profiler->getTotalDuration());
        $this->assertGreaterThan(0, $profiler->getTotalDuration('db.query'));
        $this->assertSame(0.0, $profiler->getTotalDuration('missing'));
    }

    public function testGetTotalMemoryUsageTotalsAndFiltersByName(): void
    {
        $profiler = new Profiler();
        $profiler->start('db.query')->finish();
        $profiler->start('render')->finish();

        $this->assertSame(
            $profiler->getTotalMemoryUsage('db.query') + $profiler->getTotalMemoryUsage('render'),
            $profiler->getTotalMemoryUsage()
        );
        $this->assertSame(0, $profiler->getTotalMemoryUsage('missing'));
    }

    public function testResetClearsCollectedMeasurements(): void
    {
        $profiler = new Profiler();
        $profiler->start('db.query')->finish();

        $profiler->reset();

        $this->assertSame([], $profiler->getMeasurements());
        $this->assertSame(0, $profiler->getCount());
    }

    public function testResetDoesNotResetJobIdCounter(): void
    {
        $profiler = new Profiler();
        $first = $profiler->start('a');
        $first->finish();

        $profiler->reset();

        $second = $profiler->start('a');
        $second->finish();

        $this->assertNotSame($first->id, $second->id);
    }
}
