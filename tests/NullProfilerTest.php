<?php

declare(strict_types=1);

namespace SugiPHP\Profiling\Tests;

use SugiPHP\Profiling\Job;
use SugiPHP\Profiling\JobMeasurement;
use SugiPHP\Profiling\NullProfiler;
use SugiPHP\Profiling\ProfilerInterface;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullProfiler::class)]
#[UsesClass(Job::class)]
#[UsesClass(JobMeasurement::class)]
class NullProfilerTest extends TestCase
{
    public function testImplementsProfilerInterface(): void
    {
        $this->assertInstanceOf(ProfilerInterface::class, new NullProfiler());
    }

    public function testStartReturnsAJobThatMeasuresNormally(): void
    {
        $profiler = new NullProfiler();
        $job = $profiler->start('render', ['foo' => 'bar']);

        $measurement = $job->finish();

        $this->assertInstanceOf(JobMeasurement::class, $measurement);
        $this->assertSame('render', $measurement->name);
        $this->assertSame(['foo' => 'bar'], $measurement->context);
    }

    public function testFinishingTwiceStillThrows(): void
    {
        $profiler = new NullProfiler();
        $job = $profiler->start('render');
        $job->finish();

        $this->expectException(LogicException::class);
        $job->finish();
    }

    public function testJobsFromDifferentCallsHaveDifferentIds(): void
    {
        $profiler = new NullProfiler();
        $a = $profiler->start('a');
        $b = $profiler->start('b');

        $this->assertNotSame($a->id, $b->id);

        $a->finish();
        $b->finish();
    }
}
