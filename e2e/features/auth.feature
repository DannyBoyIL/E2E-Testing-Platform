Feature: Authentication
  As a user of the E2E Testing Platform
  I want to authenticate securely
  So that I can access protected resources

  Background:
    Given I am on the login page with a clean session

  Scenario: Login page is shown by default
    When I navigate to the root URL
    Then I should be redirected to the login page
    And the email and password fields should be visible

  Scenario: Successful login with valid credentials
    When I login with email "admin@test.com" and password "password123"
    Then I should be redirected to the dashboard
    And I should see "E2E Testing Platform"

  Scenario: Error shown for invalid credentials
    When I login with email "wrong@test.com" and password "wrongpassword"
    Then I should see the error message "Invalid credentials"

  Scenario: Navigate to register page from login
    When I click the Register link
    Then I should be on the register page
    And the Name field should be visible

  Scenario: Register a new account
    Given I am on the register page
    When I register with name "New User" and a unique email and password "password123"
    Then I should be redirected to the dashboard
