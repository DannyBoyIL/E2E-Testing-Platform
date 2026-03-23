Feature: Orders API
  As an authenticated user
  I want to access the orders REST API
  So that I can view and manage order records

  Scenario: Unauthenticated access to orders list is rejected
    When I GET "/api/orders"
    Then the response status should be 401

  Scenario: Authenticated user can retrieve the orders list
    Given I am authenticated as "admin@test.com" with password "password123"
    When I GET "/api/orders"
    Then the response status should be 200
    And the response should not be empty

  Scenario: Authenticated user can create an order
    Given I am authenticated as "admin@test.com" with password "password123"
    When I POST to "/api/orders" with:
      | status | pending |
      | total  | 99.99   |
    Then the response status should be 201
    And the response should contain "data"
    And the response data should contain "id"
    And the response data should contain "status"
    And the response data should contain "total"
