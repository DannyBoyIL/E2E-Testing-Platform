import {test, expect} from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('Authentication', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/login');
        await page.evaluate(() => localStorage.clear());
    });

    test('shows login page by default', async ({page}) => {
        await page.goto('/');
        await expect(page).toHaveURL(/login/);
        await expect(page.getByPlaceholder('Email').first()).toBeVisible();
        await expect(page.getByPlaceholder('Password').first()).toBeVisible();
    });

    test('user can login with valid credentials', async ({page}) => {
        await page.goto('/login');
        await page.getByPlaceholder('Email').fill('admin@test.com');
        await page.getByPlaceholder('Password').fill('password123');
        await page.getByRole('button', {name: 'Login'}).click();
        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();
    });

    test('user sees error with invalid credentials', async ({page}) => {
        await page.goto('/login');
        await page.evaluate(() => localStorage.clear());
        await page.goto('/login');
        await page.getByPlaceholder('Email').first().fill('wrong@test.com');
        await page.getByPlaceholder('Password').first().fill('wrongpassword');
        await page.getByRole('button', {name: 'Login'}).click();
        await expect(page.getByText('Invalid credentials')).toBeVisible();
    });

    test('user can navigate to register page', async ({page}) => {
        await page.goto('/login');
        await page.getByRole('link', {name: 'Register'}).click();
        await expect(page).toHaveURL(/register/);
        await expect(page.getByPlaceholder('Name')).toBeVisible();
    });

    test('user can register a new account', async ({page}) => {
        await page.goto('/register');
        await page.getByPlaceholder('Name').fill('New User');
        await page.getByPlaceholder('Email').fill(`newuser${Date.now()}@test.com`);
        await page.getByPlaceholder('Password', {exact: true}).fill('password123');
        await page.getByPlaceholder('Confirm Password').fill('password123');
        await page.getByRole('button', {name: 'Register'}).click();
        await expect(page).toHaveURL(BASE_URL + '/');
    });
});
