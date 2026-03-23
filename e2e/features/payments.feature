Feature: Payments
  As an authenticated user
  I want to view payment records
  So that I can track payment amounts, statuses, and methods

  Background:
    Given I am logged in as "admin@test.com" with password "password123"

  Scenario: Navigate to payments page
    When I click the "Payments" navigation link
    Then I should be on the payments page

  Scenario: Payments table is visible with correct columns
    Given I am on the payments page
    Then a data table should be visible
    And the table should have an "Amount" column
    And the table should have a "Status" column
    And the table should have a "Method" column

  Scenario: Unauthenticated user is redirected to login
    Given I have cleared my session
    When I navigate to the payments page
    Then I should be redirected to the login page
