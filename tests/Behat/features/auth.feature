Feature: Authentication API
  As a client application
  I want to authenticate via the REST API
  So that I can access protected resources

  Scenario: Register a new user
    When I POST to "/api/auth/register" with:
      | name                  | Behat User          |
      | email                 | behatuser@test.com  |
      | password              | password123         |
      | password_confirmation | password123         |
    Then the response status should be 201
    And the response should contain "token"
    And the response should contain "user"

  Scenario: Login with valid credentials
    When I POST to "/api/auth/login" with:
      | email    | admin@test.com |
      | password | password123    |
    Then the response status should be 200
    And the response should contain "token"
    And the response should contain "user"

  Scenario: Login with invalid credentials returns 401
    When I POST to "/api/auth/login" with:
      | email    | wrong@test.com |
      | password | wrongpassword  |
    Then the response status should be 401
    And the response field "message" should be "Invalid credentials"

  Scenario: Get current authenticated user
    Given I am authenticated as "admin@test.com" with password "password123"
    When I GET "/api/auth/me"
    Then the response status should be 200
    And the response field "email" should be "admin@test.com"

  Scenario: Accessing /me without authentication returns 401
    When I GET "/api/auth/me"
    Then the response status should be 401

  Scenario: Logout invalidates the session token
    Given I am authenticated as "admin@test.com" with password "password123"
    When I POST to "/api/auth/logout"
    Then the response status should be 200
    And the response field "message" should be "Logged out successfully"
