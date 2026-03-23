<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedSubscriber;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class FileLoggerExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $logger = new PhpUnitRunLogger();
        $facade->registerSubscribers(
            new PhpUnitRunStartedSubscriber($logger),
            new PhpUnitTestPreparationStartedSubscriber($logger),
            new PhpUnitTestPassedSubscriber($logger),
            new PhpUnitTestFailedSubscriber($logger),
            new PhpUnitTestErroredSubscriber($logger),
            new PhpUnitTestSkippedSubscriber($logger),
            new PhpUnitTestIncompleteSubscriber($logger),
            new PhpUnitTestFinishedSubscriber($logger),
            new PhpUnitRunFinishedSubscriber($logger),
        );
    }
}

// ─── Logger ──────────────────────────────────────────────────────────────────

final class PhpUnitRunLogger
{
    private string $logPath = '';
    private float $runStart = 0.0;
    private bool $hadFailure = false;
    /** @var array<string, float> */
    private array $starts = [];
    /** @var array<string, string> */
    private array $outcomes = [];
    /** @var array<string, string> */
    private array $errors = [];

    public function onRunStart(): void
    {
        $this->runStart = microtime(true);
        $dir = getcwd() . '/storage/logs/phpunit';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $t = $this->runStart;
        $sec = (int)$t;
        $ms = sprintf('%03d', (int)round(($t - $sec) * 1000));
        $now = new DateTimeImmutable('@' . $sec);
        $stamp = $now->format('Y-m-d\TH-i-s') . '-' . $ms . 'Z';
        $this->logPath = "{$dir}/test-run-{$stamp}.log";
        file_put_contents(
            $this->logPath,
            'Test run started: ' . $now->format(DateTimeInterface::ATOM) . "\n" .
            str_repeat('─', 80) . "\n\n"
        );
    }

    public function onTestStart(string $id): void
    {
        $this->starts[$id] = microtime(true);
    }

    public function setOutcome(string $id, string $status, string $error = ''): void
    {
        $this->outcomes[$id] = $status;
        if ($error !== '') {
            $this->errors[$id] = trim(explode("\n", $error)[0]);
        }
        if ($status === 'FAILED' || $status === 'ERROR') {
            $this->hadFailure = true;
        }
    }

    public function onTestFinish(Test $test): void
    {
        if ($this->logPath === '') {
            return;
        }
        $id = $test->id();
        $ms = isset($this->starts[$id])
            ? (int)round((microtime(true) - $this->starts[$id]) * 1000)
            : 0;
        $status = $this->outcomes[$id] ?? 'UNKNOWN';
        $title = $test instanceof TestMethod
            ? $test->className() . ' › ' . $test->methodName()
            : $id;

        file_put_contents($this->logPath, sprintf("[%-8s] %6dms  %s\n", $status, $ms, $title), FILE_APPEND);
        if (isset($this->errors[$id])) {
            file_put_contents($this->logPath, "           ↳ {$this->errors[$id]}\n", FILE_APPEND);
        }
        unset($this->starts[$id], $this->outcomes[$id], $this->errors[$id]);
    }

    public function onRunFinish(): void
    {
        if ($this->logPath === '') {
            return;
        }
        $ms = (int)round((microtime(true) - $this->runStart) * 1000);
        $status = $this->hadFailure ? 'FAILED' : 'PASSED';
        file_put_contents(
            $this->logPath,
            "\n" . str_repeat('─', 80) . "\nFinished: {$status}  ({$ms}ms)\n",
            FILE_APPEND
        );
        echo "\n📋 Test log: {$this->logPath}\n";
    }
}

// ─── Subscribers ─────────────────────────────────────────────────────────────

final class PhpUnitRunStartedSubscriber implements ExecutionStartedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(ExecutionStarted $event): void
    {
        $this->logger->onRunStart();
    }
}

final class PhpUnitTestPreparationStartedSubscriber implements PreparationStartedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(PreparationStarted $event): void
    {
        $this->logger->onTestStart($event->test()->id());
    }
}

final class PhpUnitTestPassedSubscriber implements PassedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(Passed $event): void
    {
        $this->logger->setOutcome($event->test()->id(), 'PASSED');
    }
}

final class PhpUnitTestFailedSubscriber implements FailedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(Failed $event): void
    {
        $this->logger->setOutcome($event->test()->id(), 'FAILED', $event->throwable()->message());
    }
}

final class PhpUnitTestErroredSubscriber implements ErroredSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(Errored $event): void
    {
        $this->logger->setOutcome($event->test()->id(), 'ERROR', $event->throwable()->message());
    }
}

final class PhpUnitTestSkippedSubscriber implements SkippedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(Skipped $event): void
    {
        $this->logger->setOutcome($event->test()->id(), 'SKIPPED');
    }
}

final class PhpUnitTestIncompleteSubscriber implements MarkedIncompleteSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(MarkedIncomplete $event): void
    {
        $this->logger->setOutcome($event->test()->id(), 'INCOMPLETE');
    }
}

final class PhpUnitTestFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(Finished $event): void
    {
        $this->logger->onTestFinish($event->test());
    }
}

final class PhpUnitRunFinishedSubscriber implements ExecutionFinishedSubscriber
{
    public function __construct(private readonly PhpUnitRunLogger $logger)
    {
    }

    public function notify(ExecutionFinished $event): void
    {
        $this->logger->onRunFinish();
    }
}
