# SugiPHP\Profiling

**Version 1.0**

Lightweight timing/memory profiling: measure how long a piece of code took to run and how much memory it used, optionally collecting the results centrally.

## Components

| Class | Description |
|---|---|
| `JobInterface` | Interface for a single measured unit of work: `finish()` and `isFinished()` |
| `Job` | Default `JobInterface` implementation: measures between construction and `finish()` |
| `JobMeasurement` | Immutable snapshot returned by `finish()`: id, name, context, start time, duration, memory usage |
| `JobFinishListener` | Interface for anything that wants to be notified when a `Job` finishes |
| `ProfilerInterface` | Interface for anything that can start a job; `start()` returns a `JobInterface` |
| `Profiler` | Creates `Job`s and collects the `JobMeasurement` from each one that finishes |
| `NullProfiler` | No-op `ProfilerInterface`: jobs it starts time normally but are never recorded |

## Usage

### Standalone job

```php
use SugiPHP\Profiling\Job;

$job = new Job('db.query', ['sql' => 'SELECT * FROM users']);
// ... do the work ...
$measurement = $job->finish();

echo $measurement->name . ': ' . round($measurement->duration * 1000) . ' ms';
```

Additional context can be merged in at `finish()` time:

```php
$measurement = $job->finish(['rows' => 42]);
```

If a `Job` is destroyed without ever calling `finish()`, it emits an `E_USER_WARNING` — usually a sign of a missing `try/finally` in the calling code. Calling `finish()` twice throws a `LogicException`.

### Profiler

`Profiler` implements `ProfilerInterface`: it creates jobs, assigns each one a unique id, and collects a `JobMeasurement` every time one finishes:

```php
use SugiPHP\Profiling\Profiler;

$profiler = new Profiler();

$job = $profiler->start('db.query', ['sql' => 'SELECT 1']);
// ... do the work ...
$job->finish();

$profiler->getCount();                 // total number of finished jobs
$profiler->getCount('db.query');       // finished jobs with that name
$profiler->getTotalDuration('db.query'); // total seconds spent in that name
$profiler->getTotalMemoryUsage('db.query'); // total bytes used by that name
$profiler->getMeasurements();          // all JobMeasurement instances
$profiler->reset();                    // discard collected measurements
```

### Disabling profiling

`NullProfiler` also implements `ProfilerInterface`. Its jobs still time and measure normally — `finish()` still returns a `JobMeasurement`, still throws on double-finish, still warns on `__destruct()` if never finished — they're just never recorded anywhere, since no listener is attached. Depend on `ProfilerInterface` in your code and swap the implementation (e.g. via DI configuration) to disable profiling without `if ($profiler !== null)` checks at every call site:

```php
use SugiPHP\Profiling\ProfilerInterface;
use SugiPHP\Profiling\NullProfiler;

function handleRequest(ProfilerInterface $profiler): void
{
    $job = $profiler->start('render');
    // ... do the work ...
    $job->finish();
}

handleRequest(new NullProfiler()); // profiling disabled
```

### Custom listener

Implement `JobFinishListener` to react to jobs from something other than `Profiler` (e.g. to log each one):

```php
use SugiPHP\Profiling\Job;
use SugiPHP\Profiling\JobFinishListener;
use SugiPHP\Profiling\JobMeasurement;

class LoggingListener implements JobFinishListener
{
    public function onJobFinish(JobMeasurement $measurement): void
    {
        // log $measurement->name, $measurement->duration, ...
    }
}

$job = new Job('render', listener: new LoggingListener());
$job->finish();
```

If the listener's `onJobFinish()` throws, `finish()` marks the job as finished (it can't be retried) and rethrows the failure wrapped in a `JobFinishListenerException`, which still gives you access to the measurement that was computed:

```php
use SugiPHP\Profiling\JobFinishListenerException;

try {
    $job->finish();
} catch (JobFinishListenerException $e) {
    $measurement = $e->getMeasurement(); // still available despite the listener failure
}
```
