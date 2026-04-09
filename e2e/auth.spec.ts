import { test, expect } from './fixtures';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('Authentication', () => {

    test.beforeEach(async ({ page }) => {
        await page.evaluate(() => localStorage.clear());
        await page.goto('/login');
    });

    test('shows login page by default', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL(/login/);
        await expect(page.getByPlaceholder('Email').first()).toBeVisible();
        await expect(page.getByPlaceholder('Password').first()).toBeVisible();
    });

    test('user can login with valid credentials', async ({ loginPage, page }) => {
        await loginPage.login('admin@test.com', 'password123');
        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();
    });

    test('user sees error with invalid credentials', async ({ loginPage, page }) => {
        await loginPage.login('wrong@test.com', 'wrongpassword');
        await expect(page.getByText('Invalid credentials')).toBeVisible();
    });

    test('user can navigate to register page', async ({ page }) => {
        await page.getByRole('link', { name: 'Register' }).click();
        await expect(page).toHaveURL(/register/);
        await expect(page.getByPlaceholder('Name')).toBeVisible();
    });

    test('user can register a new account', async ({ registerPage, page }) => {
        await registerPage.goto();
        await registerPage.register('New User', `newuser${Date.now()}@test.com`, 'password123');
        await expect(page).toHaveURL(BASE_URL + '/');
    });
});
