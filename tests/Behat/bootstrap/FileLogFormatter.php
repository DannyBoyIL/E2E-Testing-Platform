<?php

declare(strict_types=1);

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\BeforeExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class FileLogFormatter implements Formatter
{
    private string $logPath = '';
    private float $runStart = 0.0;
    private float $scenarioStart = 0.0;
    private bool $hadFailure = false;

    public static function getSubscribedEvents(): array
    {
        return [
            ExerciseCompleted::BEFORE => 'onBeforeExercise',
            ScenarioTested::BEFORE => 'onBeforeScenario',
            ScenarioTested::AFTER => 'onAfterScenario',
            ExerciseCompleted::AFTER => 'onAfterExercise',
        ];
    }

    public function getName(): string
    {
        return 'file_log';
    }

    public function getDescription(): string
    {
        return 'Writes a structured log file per test run';
    }

    public function getOutputPrinter(): OutputPrinter
    {
        return new NullOutputPrinter();
    }

    public function setParameter($name, $value): void
    {
    }

    public function getParameter($name): mixed
    {
        return null;
    }

    public function onBeforeExercise(BeforeExerciseCompleted $event): void
    {
        $this->runStart = microtime(true);
        $this->hadFailure = false;

        $dir = getcwd() . '/storage/logs/behat';
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

    public function onBeforeScenario(BeforeScenarioTested $event): void
    {
        $this->scenarioStart = microtime(true);
    }

    public function onAfterScenario(AfterScenarioTested $event): void
    {
        if ($this->logPath === '') {
            return;
        }
        $ms = (int)round((microtime(true) - $this->scenarioStart) * 1000);
        $resultCode = $event->getTestResult()->getResultCode();
        $status = match ($resultCode) {
            TestResult::PASSED => 'PASSED',
            TestResult::FAILED => 'FAILED',
            TestResult::SKIPPED => 'SKIPPED',
            default => 'PENDING',
        };
        if ($resultCode === TestResult::FAILED) {
            $this->hadFailure = true;
        }

        $feature = $event->getFeature()->getTitle() ?? 'Unknown Feature';
        $scenario = $event->getScenario()->getTitle() ?? 'Unknown Scenario';

        file_put_contents(
            $this->logPath,
            sprintf("[%-8s] %6dms  behat › %s › %s\n", $status, $ms, $feature, $scenario),
            FILE_APPEND
        );
    }

    public function onAfterExercise(AfterExerciseCompleted $event): void
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

// ─── Minimal no-op printer (we write directly to file) ───────────────────────

final class NullOutputPrinter implements OutputPrinter
{
    public function setOutputPath($path): void
    {
    }

    public function getOutputPath(): ?string
    {
        return null;
    }

    public function setOutputStyles(array $styles): void
    {
    }

    public function getOutputStyles(): array
    {
        return [];
    }

    public function setOutputDecorated($decorated): void
    {
    }

    public function isOutputDecorated(): ?bool
    {
        return null;
    }

    public function setOutputVerbosity($level): void
    {
    }

    public function getOutputVerbosity(): int
    {
        return self::VERBOSITY_NORMAL;
    }

    public function write($messages): void
    {
    }

    public function writeln($messages = ''): void
    {
    }

    public function flush(): void
    {
    }
}
