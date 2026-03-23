Feature: Payments API
  As an authenticated user
  I want to access the payments REST API
  So that I can view and manage payment records

  Scenario: Unauthenticated access to payments list is rejected
    When I GET "/api/payments"
    Then the response status should be 401

  Scenario: Authenticated user can retrieve the payments list
    Given I am authenticated as "admin@test.com" with password "password123"
    When I GET "/api/payments"
    Then the response status should be 200
    And the response should not be empty
