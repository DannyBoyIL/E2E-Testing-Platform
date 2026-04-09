import { test, expect } from './fixtures';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('User Flows', () => {

    test('full registration → dashboard flow', async ({ registerPage, page }) => {
        const email = `playwright${Date.now()}@test.com`;

        await registerPage.goto();
        await registerPage.register('Playwright User', email, 'password123');

        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();

        await expect(page.getByRole('link', { name: 'Users' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Orders' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Payments' })).toBeVisible();
    });

    test('login with wrong credentials → see error → correct login → dashboard', async ({ loginPage, page }) => {
        await page.goto('/login');

        await loginPage.login('wrong@test.com', 'wrongpassword');
        await expect(page.getByText('Invalid credentials')).toBeVisible();

        await loginPage.login('admin@test.com', 'password123');
        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();
    });

    test('login → navigate all sections → verify data exists', async ({ authenticatedPage: page }) => {
        await page.getByRole('link', { name: 'Users' }).click();
        await expect(page).toHaveURL(/users/);
        await expect(page.locator('tbody tr').first()).toBeVisible();

        await page.goto('/');
        await page.getByRole('link', { name: 'Orders' }).click();
        await expect(page).toHaveURL(/orders/);
        await expect(page.locator('tbody tr').first()).toBeVisible();

        await page.goto('/');
        await page.getByRole('link', { name: 'Payments' }).click();
        await expect(page).toHaveURL(/payments/);
        await expect(page.getByRole('table')).toBeVisible();
    });

    test('unauthenticated access to protected routes redirects to login', async ({ page }) => {
        const protectedRoutes = ['/', '/users', '/orders', '/payments'];

        for (const route of protectedRoutes) {
            await page.goto(route);
            await expect(page).toHaveURL(/login/);
        }
    });
});
