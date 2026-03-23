import { expect } from '@playwright/test';
import { createBdd } from 'playwright-bdd';

const { Given, When, Then } = createBdd();

const BASE_URL = 'http://127.0.0.1:8000';

// ─── Setup / Session ────────────────────────────────────────────────────────

Given('I am on the login page with a clean session', async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
});

Given('I am on the login page', async ({ page }) => {
    await page.goto('/login');
});

Given('I am on the register page', async ({ page }) => {
    await page.goto('/register');
});

Given('I am logged in as {string} with password {string}', async ({ page }, email: string, password: string) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
    await page.getByPlaceholder('Email').fill(email);
    await page.getByPlaceholder('Password').fill(password);
    await page.getByRole('button', { name: 'Login' }).click();
    await page.waitForURL('/');
});

Given('I have cleared my session', async ({ page }) => {
    await page.goto('/login');
    await page.evaluate(() => localStorage.clear());
});

// ─── Navigation ─────────────────────────────────────────────────────────────

Given('I am on the users page', async ({ page }) => {
    await page.goto('/users');
});

Given('I am on the orders page', async ({ page }) => {
    await page.goto('/orders');
});

Given('I am on the payments page', async ({ page }) => {
    await page.goto('/payments');
});

When('I navigate to the root URL', async ({ page }) => {
    await page.goto('/');
});

When('I navigate to the dashboard', async ({ page }) => {
    await page.goto('/');
});

When('I navigate to the users page', async ({ page }) => {
    await page.goto('/users');
});

When('I navigate to the orders page', async ({ page }) => {
    await page.goto('/orders');
});

When('I navigate to the payments page', async ({ page }) => {
    await page.goto('/payments');
});

When('I click the {string} navigation link', async ({ page }, name: string) => {
    await page.getByRole('link', { name }).click();
});

// ─── Auth actions ────────────────────────────────────────────────────────────

When('I login with email {string} and password {string}', async ({ page }, email: string, password: string) => {
    await page.getByPlaceholder('Email').fill(email);
    await page.getByPlaceholder('Password').fill(password);
    await page.getByRole('button', { name: 'Login' }).click();
});

// ─── URL assertions ──────────────────────────────────────────────────────────

Then('I should be redirected to the login page', async ({ page }) => {
    await expect(page).toHaveURL(/login/);
});

Then('I should be redirected to the dashboard', async ({ page }) => {
    await expect(page).toHaveURL(BASE_URL + '/');
});

Then('I should be on the users page', async ({ page }) => {
    await expect(page).toHaveURL(/users/);
});

Then('I should be on the orders page', async ({ page }) => {
    await expect(page).toHaveURL(/orders/);
});

Then('I should be on the payments page', async ({ page }) => {
    await expect(page).toHaveURL(/payments/);
});

// ─── Content assertions ───────────────────────────────────────────────────────

Then('I should see {string}', async ({ page }, text: string) => {
    await expect(page.getByText(text)).toBeVisible();
});

Then('I should see the {string} navigation link', async ({ page }, name: string) => {
    await expect(page.getByRole('link', { name })).toBeVisible();
});

// ─── Table assertions ─────────────────────────────────────────────────────────

Then('a data table should be visible', async ({ page }) => {
    await expect(page.getByRole('table')).toBeVisible();
});

Then('the table should have a {string} column', async ({ page }, columnName: string) => {
    await expect(page.getByRole('columnheader', { name: columnName })).toBeVisible();
});

Then('the table should have an {string} column', async ({ page }, columnName: string) => {
    await expect(page.getByRole('columnheader', { name: columnName })).toBeVisible();
});

Then('the table should contain at least one row', async ({ page }) => {
    const rows = page.locator('tbody tr');
    await expect(rows.first()).toBeVisible();
    expect(await rows.count()).toBeGreaterThan(0);
});
