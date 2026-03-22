import { test, expect } from '@playwright/test';

test.describe('Payments', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', { name: 'Login' }).click();
        await page.waitForURL('/');
    });

    test('user can navigate to payments page', async ({ page }) => {
        await page.getByRole('link', { name: 'Payments' }).click();
        await expect(page).toHaveURL(/payments/);
    });

    test('payments table is visible with correct columns', async ({ page }) => {
        await page.goto('/payments');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Amount' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Status' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Method' })).toBeVisible();
    });

    test('unauthenticated user is redirected to login', async ({ page }) => {
        await page.evaluate(() => localStorage.clear());
        await page.goto('/payments');
        await expect(page).toHaveURL(/login/);
    });
});
