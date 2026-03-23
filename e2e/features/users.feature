Feature: Users
  As an authenticated user
  I want to manage users
  So that I can view and interact with user data

  Background:
    Given I am logged in as "admin@test.com" with password "password123"

  Scenario: Navigate to users page
    When I click the "Users" navigation link
    Then I should be on the users page

  Scenario: Users table is visible with correct columns
    Given I am on the users page
    Then a data table should be visible
    And the table should have a "Name" column
    And the table should have an "Email" column

  Scenario: Users table contains seeded users
    Given I am on the users page
    Then the table should contain at least one row

  Scenario: Unauthenticated user is redirected to login
    Given I have cleared my session
    When I navigate to the users page
    Then I should be redirected to the login page
