import { Page, Locator } from '@playwright/test';

export class RegisterPage {

    readonly nameInput: Locator;
    readonly emailInput: Locator;
    readonly passwordInput: Locator;
    readonly confirmPasswordInput: Locator;
    readonly registerButton: Locator;

    constructor(private readonly page: Page) {
        this.nameInput = page.getByPlaceholder('Name');
        this.emailInput = page.getByPlaceholder('Email');
        this.passwordInput = page.getByPlaceholder('Password', { exact: true });
        this.confirmPasswordInput = page.getByPlaceholder('Confirm Password');
        this.registerButton = page.getByRole('button', { name: 'Register' });
    }

    async goto() {
        await this.page.goto('/register');
    }

    async register(name: string, email: string, password: string) {
        await this.nameInput.fill(name);
        await this.emailInput.fill(email);
        await this.passwordInput.fill(password);
        await this.confirmPasswordInput.fill(password);
        await this.registerButton.click();
    }
}
