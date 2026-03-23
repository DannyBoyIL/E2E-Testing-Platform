import {test, expect} from '@playwright/test';

test.describe('Orders', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/login');
        await page.evaluate(() => localStorage.clear());
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', {name: 'Login'}).click();
        await page.waitForURL('/');
    });

    test('user can navigate to orders page', async ({page}) => {
        await page.getByRole('link', {name: 'Orders'}).click();
        await expect(page).toHaveURL(/orders/);
    });

    test('orders table is visible with correct columns', async ({page}) => {
        await page.goto('/orders');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('columnheader', {name: 'Status'})).toBeVisible();
        await expect(page.getByRole('columnheader', {name: 'Total'})).toBeVisible();
    });

    test('orders table contains seeded orders', async ({page}) => {
        await page.goto('/orders');
        const rows = page.locator('tbody tr');
        await expect(rows.first()).toBeVisible();
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('unauthenticated user is redirected to login', async ({page}) => {
        await page.evaluate(() => localStorage.clear());
        await page.goto('/orders');
        await expect(page).toHaveURL(/login/);
    });
});
