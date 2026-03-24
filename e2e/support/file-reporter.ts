import type { Reporter, TestCase, TestResult, FullResult, Suite, FullConfig } from '@playwright/test/reporter';
import fs from 'fs';
import path from 'path';

class FileReporter implements Reporter {
    private logPaths = new Map<string, string>();
    private startTime!: Date;
    private runStamp!: string;

    onBegin(_config: FullConfig, _suite: Suite): void {
        this.startTime = new Date();
        this.runStamp = this.startTime.toISOString().replace(/[:.]/g, '-');
        const baseDir = path.join(process.cwd(), 'storage', 'logs');
        if (!fs.existsSync(baseDir)) {
            fs.mkdirSync(baseDir, { recursive: true });
        }
    }

    private getLogPath(projectName: string): string {
        const safeName = projectName && projectName.trim() ? projectName : 'playwright';
        const existing = this.logPaths.get(safeName);
        if (existing) {
            return existing;
        }

        const logsDir = path.join(process.cwd(), 'storage', 'logs', safeName);
        if (!fs.existsSync(logsDir)) {
            fs.mkdirSync(logsDir, { recursive: true });
        }

        const logPath = path.join(logsDir, `test-run-${this.runStamp}.log`);
        fs.writeFileSync(
            logPath,
            `Test run started: ${this.startTime.toISOString()}\n${'─'.repeat(80)}\n\n`,
        );
        this.logPaths.set(safeName, logPath);
        return logPath;
    }

    private getProjectName(test: TestCase): string {
        let suite = test.parent;
        while (suite) {
            if (suite.type === 'project') {
                return suite.title || 'playwright';
            }
            suite = suite.parent;
        }
        return 'playwright';
    }

    onTestEnd(test: TestCase, result: TestResult): void {
        const status = result.status.toUpperCase().padEnd(7);
        const duration = `${result.duration}ms`.padStart(8);
        const title = test.titlePath().slice(1).join(' › ');
        const projectName = this.getProjectName(test);
        const logPath = this.getLogPath(projectName);
        fs.appendFileSync(logPath, `[${status}] ${duration}  ${title}\n`);

        for (const err of result.errors) {
            const msg = (err.message ?? 'Unknown error').split('\n')[0];
            fs.appendFileSync(logPath, `           ↳ ${msg}\n`);
        }
    }

    onEnd(result: FullResult): void {
        const duration = Date.now() - this.startTime.getTime();
        for (const logPath of this.logPaths.values()) {
            fs.appendFileSync(
                logPath,
                `\n${'─'.repeat(80)}\nFinished: ${result.status.toUpperCase()}  (${duration}ms)\n`,
            );
            console.log(`\n📋 Test log: ${logPath}`);
        }
    }
}

export default FileReporter;
