import { expect } from '@playwright/test';
import { createBdd } from 'playwright-bdd';

const { When, Then } = createBdd();

Then('the email and password fields should be visible', async ({ page }) => {
    await expect(page.getByPlaceholder('Email').first()).toBeVisible();
    await expect(page.getByPlaceholder('Password').first()).toBeVisible();
});

Then('I should see the error message {string}', async ({ page }, message: string) => {
    await expect(page.getByText(message)).toBeVisible();
});

When('I click the Register link', async ({ page }) => {
    await page.getByRole('link', { name: 'Register' }).click();
});

Then('I should be on the register page', async ({ page }) => {
    await expect(page).toHaveURL(/register/);
});

Then('the Name field should be visible', async ({ page }) => {
    await expect(page.getByPlaceholder('Name')).toBeVisible();
});

When('I register with name {string} and a unique email and password {string}', async ({ page }, name: string, password: string) => {
    await page.getByPlaceholder('Name').fill(name);
    await page.getByPlaceholder('Email').fill(`playwright${Date.now()}@test.com`);
    await page.getByPlaceholder('Password', { exact: true }).fill(password);
    await page.getByPlaceholder('Confirm Password').fill(password);
    await page.getByRole('button', { name: 'Register' }).click();
});
