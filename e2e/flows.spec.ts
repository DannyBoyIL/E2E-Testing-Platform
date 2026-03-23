import {test, expect} from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('User Flows', () => {

    test('full registration → dashboard flow', async ({page}) => {
        const email = `playwright${Date.now()}@test.com`;

        await page.goto('/register');
        await page.getByPlaceholder('Name').fill('Playwright User');
        await page.getByPlaceholder('Email').fill(email);
        await page.getByPlaceholder('Password', {exact: true}).fill('password123');
        await page.getByPlaceholder('Confirm Password').fill('password123');
        await page.getByRole('button', {name: 'Register'}).click();

        // Should land on dashboard
        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();

        // Dashboard links should be visible
        await expect(page.getByRole('link', {name: 'Users'})).toBeVisible();
        await expect(page.getByRole('link', {name: 'Orders'})).toBeVisible();
        await expect(page.getByRole('link', {name: 'Payments'})).toBeVisible();
    });

    test('login with wrong credentials → see error → correct login → dashboard', async ({page}) => {
        await page.goto('/login');

        // Try wrong credentials first
        await page.getByPlaceholder('Email').fill('wrong@test.com');
        await page.getByPlaceholder('Password').fill('wrongpassword');
        await page.getByRole('button', {name: 'Login'}).click();
        await expect(page.getByText('Invalid credentials')).toBeVisible();

        // Correct credentials
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', {name: 'Login'}).click();

        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();
    });

    test('login → navigate all sections → verify data exists', async ({page}) => {
        await page.goto('/login');
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', {name: 'Login'}).click();
        await page.waitForURL('/');

        // Users section
        await page.getByRole('link', {name: 'Users'}).click();
        await expect(page).toHaveURL(/users/);
        await expect(page.locator('tbody tr').first()).toBeVisible();

        // Orders section
        await page.goto('/');
        await page.getByRole('link', {name: 'Orders'}).click();
        await expect(page).toHaveURL(/orders/);
        await expect(page.locator('tbody tr').first()).toBeVisible();

        // Payments section
        await page.goto('/');
        await page.getByRole('link', {name: 'Payments'}).click();
        await expect(page).toHaveURL(/payments/);
        await expect(page.getByRole('table')).toBeVisible();
    });

    test('unauthenticated access to protected routes redirects to login', async ({page}) => {
        await page.goto('/login');
        await page.evaluate(() => localStorage.clear());

        const protectedRoutes = ['/', '/users', '/orders', '/payments'];

        for (const route of protectedRoutes) {
            await page.goto(route);
            await expect(page).toHaveURL(/login/);
        }
    });
});
