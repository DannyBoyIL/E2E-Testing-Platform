Feature: Users API
  As an authenticated user
  I want to access the users REST API
  So that I can retrieve and manage user data

  Scenario: Unauthenticated access to users list is rejected
    When I GET "/api/users"
    Then the response status should be 401

  Scenario: Authenticated user can retrieve the users list
    Given I am authenticated as "admin@test.com" with password "password123"
    When I GET "/api/users"
    Then the response status should be 200
    And the response should not be empty

  Scenario: Authenticated user can retrieve a specific user
    Given I am authenticated as "admin@test.com" with password "password123"
    When I GET "/api/users/1"
    Then the response status should be 200
    And the response should contain "data"
    And the response data should contain "name"
    And the response data should contain "email"
