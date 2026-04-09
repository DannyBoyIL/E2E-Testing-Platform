import { test, expect } from './fixtures';

test.describe('Orders', () => {

    test('user can navigate to orders page', async ({ authenticatedPage: page }) => {
        await page.getByRole('link', { name: 'Orders' }).click();
        await expect(page).toHaveURL(/orders/);
    });

    test('orders table is visible with correct columns', async ({ authenticatedPage: page }) => {
        await page.goto('/orders');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Status' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Total' })).toBeVisible();
    });

    test('orders table contains seeded orders', async ({ authenticatedPage: page }) => {
        await page.goto('/orders');
        const rows = page.locator('tbody tr');
        await expect(rows.first()).toBeVisible();
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('unauthenticated user is redirected to login', async ({ page }) => {
        await page.goto('/orders');
        await expect(page).toHaveURL(/login/);
    });
});
