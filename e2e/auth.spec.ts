import {test, expect} from './fixtures';

const BASE_URL = 'http://127.0.0.1:8000';

test.describe('Authentication', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/login');
    });

    test('shows login page by default', async ({page}) => {
        await page.goto('/');
        await expect(page).toHaveURL(/login/);
        await expect(page.getByPlaceholder('Email').first()).toBeVisible();
        await expect(page.getByPlaceholder('Password').first()).toBeVisible();
    });

    test('user can login with valid credentials', async ({loginPage, page}) => {
        await loginPage.login(process.env.TEST_USER_EMAIL!, process.env.TEST_USER_PASSWORD!);
        await expect(page).toHaveURL(BASE_URL + '/');
        await expect(page.getByText('E2E Testing Platform')).toBeVisible();
    });

    test('user sees error with invalid credentials', async ({loginPage, page}) => {
        await loginPage.login(process.env.TEST_WRONG_EMAIL!, process.env.TEST_WRONG_PASSWORD!);
        await expect(page.getByText('Invalid credentials')).toBeVisible();
    });

    test('user can navigate to register page', async ({page}) => {
        await page.getByRole('link', {name: 'Register'}).click();
        await expect(page).toHaveURL(/register/);
        await expect(page.getByPlaceholder('Name')).toBeVisible();
    });

    test('user can register a new account', async ({registerPage, page}) => {
        await registerPage.goto();
        await registerPage.register('New User', `newuser${Date.now()}@test.com`, process.env.TEST_USER_PASSWORD!);
        await expect(page).toHaveURL(BASE_URL + '/');
    });
});
