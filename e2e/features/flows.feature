Feature: User Flows
  As a user of the E2E Testing Platform
  I want end-to-end flows to work correctly
  So that I can rely on the full system behaviour

  Scenario: Full registration to dashboard flow
    Given I am on the register page
    When I register with name "Playwright User" and a unique email and password "password123"
    Then I should be redirected to the dashboard
    And I should see "E2E Testing Platform"
    And I should see the "Users" navigation link
    And I should see the "Orders" navigation link
    And I should see the "Payments" navigation link

  Scenario: Wrong credentials then correct login leads to dashboard
    Given I am on the login page
    When I login with email "wrong@test.com" and password "wrongpassword"
    Then I should see the error message "Invalid credentials"
    When I login with email "admin@test.com" and password "password123"
    Then I should be redirected to the dashboard
    And I should see "E2E Testing Platform"

  Scenario: Login and navigate all sections with data
    Given I am logged in as "admin@test.com" with password "password123"
    When I click the "Users" navigation link
    Then I should be on the users page
    And the table should contain at least one row
    When I navigate to the dashboard
    And I click the "Orders" navigation link
    Then I should be on the orders page
    And the table should contain at least one row
    When I navigate to the dashboard
    And I click the "Payments" navigation link
    Then I should be on the payments page
    And a data table should be visible

  Scenario: Unauthenticated access to all protected routes redirects to login
    Given I have cleared my session
    When I navigate to the root URL
    Then I should be redirected to the login page
    When I navigate to the users page
    Then I should be redirected to the login page
    When I navigate to the orders page
    Then I should be redirected to the login page
    When I navigate to the payments page
    Then I should be redirected to the login page
