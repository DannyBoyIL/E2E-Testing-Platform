Feature: Orders
  As an authenticated user
  I want to view my orders
  So that I can track order status and totals

  Background:
    Given I am logged in as "admin@test.com" with password "password123"

  Scenario: Navigate to orders page
    When I click the "Orders" navigation link
    Then I should be on the orders page

  Scenario: Orders table is visible with correct columns
    Given I am on the orders page
    Then a data table should be visible
    And the table should have a "Status" column
    And the table should have a "Total" column

  Scenario: Orders table contains seeded orders
    Given I am on the orders page
    Then the table should contain at least one row

  Scenario: Unauthenticated user is redirected to login
    Given I have cleared my session
    When I navigate to the orders page
    Then I should be redirected to the login page
