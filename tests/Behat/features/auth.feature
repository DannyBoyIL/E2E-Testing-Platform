Feature: Authentication API
  As a client application
  I want to authenticate via the REST API
  So that I can access protected resources

  Scenario: Register a new user
    When I register a new test user
    Then the response status should be 201
    And the response should contain "token"
    And the response should contain "user"

  Scenario: Login with valid credentials
    When I POST the admin login to "/api/auth/login"
    Then the response status should be 200
    And the response should contain "token"
    And the response should contain "user"

  Scenario: Login with invalid credentials returns 401
    When I POST invalid login credentials to "/api/auth/login"
    Then the response status should be 401
    And the response field "message" should be "Invalid credentials"

  Scenario: Get current authenticated user
    Given I am authenticated as the admin user
    When I GET "/api/auth/me"
    Then the response status should be 200
    And the response field "email" should match the admin user email

  Scenario: Accessing /me without authentication returns 401
    When I GET "/api/auth/me"
    Then the response status should be 401

  Scenario: Logout invalidates the session token
    Given I am authenticated as the admin user
    When I POST to "/api/auth/logout"
    Then the response status should be 200
    And the response field "message" should be "Logged out successfully"
