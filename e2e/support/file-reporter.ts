import type { Reporter, TestCase, TestResult, FullResult, Suite, FullConfig } from '@playwright/test/reporter';
import fs from 'fs';
import path from 'path';

class FileReporter implements Reporter {
    private logPath!: string;
    private startTime!: Date;

    onBegin(_config: FullConfig, _suite: Suite): void {
        const logsDir = path.join(process.cwd(), 'logs');
        if (!fs.existsSync(logsDir)) {
            fs.mkdirSync(logsDir, { recursive: true });
        }
        this.startTime = new Date();
        const timestamp = this.startTime.toISOString().replace(/[:.]/g, '-');
        this.logPath = path.join(logsDir, `test-run-${timestamp}.log`);
        fs.writeFileSync(
            this.logPath,
            `Test run started: ${this.startTime.toISOString()}\n${'─'.repeat(80)}\n\n`,
        );
    }

    onTestEnd(test: TestCase, result: TestResult): void {
        const status = result.status.toUpperCase().padEnd(7);
        const duration = `${result.duration}ms`.padStart(8);
        const title = test.titlePath().slice(1).join(' › ');
        fs.appendFileSync(this.logPath, `[${status}] ${duration}  ${title}\n`);

        for (const err of result.errors) {
            const msg = (err.message ?? 'Unknown error').split('\n')[0];
            fs.appendFileSync(this.logPath, `           ↳ ${msg}\n`);
        }
    }

    onEnd(result: FullResult): void {
        const duration = Date.now() - this.startTime.getTime();
        fs.appendFileSync(
            this.logPath,
            `\n${'─'.repeat(80)}\nFinished: ${result.status.toUpperCase()}  (${duration}ms)\n`,
        );
        console.log(`\n📋 Test log: ${this.logPath}`);
    }
}

export default FileReporter;
