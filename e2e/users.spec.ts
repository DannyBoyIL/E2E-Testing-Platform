import {test, expect} from '@playwright/test';

test.describe('Users', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/login');
        await page.evaluate(() => localStorage.clear());
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', {name: 'Login'}).click();
        await page.waitForURL('/');
    });

    test('user can navigate to users page', async ({page}) => {
        await page.getByRole('link', {name: 'Users'}).click();
        await expect(page).toHaveURL(/users/);
    });

    test('users table is visible with data', async ({page}) => {
        await page.goto('/users');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('columnheader', {name: 'Name'})).toBeVisible();
        await expect(page.getByRole('columnheader', {name: 'Email'})).toBeVisible();
    });

    test('users table contains seeded users', async ({page}) => {
        await page.goto('/users');
        const rows = page.locator('tbody tr');
        await expect(rows.first()).toBeVisible();
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('unauthenticated user is redirected to login', async ({page}) => {
        await page.evaluate(() => localStorage.clear());
        await page.goto('/users');
        await expect(page).toHaveURL(/login/);
    });
});
