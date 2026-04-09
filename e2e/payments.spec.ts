import { test, expect } from './fixtures';

test.describe('Payments', () => {

    test('user can navigate to payments page', async ({ authenticatedPage: page }) => {
        await page.getByRole('link', { name: 'Payments' }).click();
        await expect(page).toHaveURL(/payments/);
    });

    test('payments table is visible with correct columns', async ({ authenticatedPage: page }) => {
        await page.goto('/payments');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Amount' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Status' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Method' })).toBeVisible();
    });

    test('unauthenticated user is redirected to login', async ({ page }) => {
        await page.goto('/payments');
        await expect(page).toHaveURL(/login/);
    });
});
