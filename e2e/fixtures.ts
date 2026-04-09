import { test as base, Page } from '@playwright/test';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';

type Fixtures = {
    loginPage: LoginPage;
    registerPage: RegisterPage;
    authenticatedPage: Page;
};

export const test = base.extend<Fixtures>({
    loginPage: async ({ page }, use) => {
        await use(new LoginPage(page));
    },

    registerPage: async ({ page }, use) => {
        await use(new RegisterPage(page));
    },

    authenticatedPage: async ({ page }, use) => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login('admin@test.com', 'password123');
        await page.waitForURL('/');
        await use(page);
    },
});

export { expect } from '@playwright/test';
