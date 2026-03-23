import { defineConfig, devices } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';

const bddTestDir = defineBddConfig({
    features: './e2e/features/*.feature',
    steps: './e2e/steps/*.ts',
});

export default defineConfig({
    fullyParallel: false,
    globalTeardown: './e2e/global-teardown.ts',
    retries: 1,
    reporter: [
        ['list'],
        ['allure-playwright', { outputFolder: 'allure-results', detail: true, suiteTitle: true }],
        ['./e2e/support/file-reporter.ts'],
    ],
    use: {
        baseURL: 'http://127.0.0.1:8000',
        headless: true,
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            testDir: './e2e',
            testMatch: /.*\.spec\.ts$/,
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'chromium-bdd',
            testDir: bddTestDir,
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
