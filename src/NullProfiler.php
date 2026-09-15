<?php

declare(strict_types=1);

namespace SugiPHP\Profiling;

/**
 * A ProfilerInterface that performs no collection or reporting.
 *
 * Jobs it starts behave as real timers (finish() still works, still
 * returns a JobMeasurement, still throws on double-finish, still warns
 * on __destruct if never finished) — they simply have no listener, so
 * nothing is recorded anywhere.
 *
 * Useful for disabling profiling in production via DI configuration,
 * without littering call sites with `if ($profiler !== null)` checks.
 */
class NullProfiler implements ProfilerInterface
{
    public function start(string $name, array $context = []): Job
    {
        return new Job(name: $name, context: $context);
    }
}
