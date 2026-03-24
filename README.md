# E2E Testing Platform (Laravel · React · PHPUnit · Behat · Playwright)
A full-stack portfolio project that simulates real-world user flows — authentication, CRUD operations, orders, and payment processing — while demonstrating a complete, layered testing strategy using backend unit/feature tests, BDD API tests, browser-based end-to-end tests, structured logging, and unified Allure reporting.

## Features Under Test
This platform targets four core domains:
* **Auth** → register, login, logout, profile
* **Users** → list, view, update, delete
* **Orders** → create, list, view, update, delete, ownership protection
* **Payments** → process, list, view, ownership protection, duplicate prevention

## Highlights
* **API-first Laravel backend** with domain-separated controllers, services, and form requests
* **React + Vite SPA frontend** fully decoupled from the backend, consuming the API via Axios
* **Laravel Sanctum** token-based authentication for SPA flows
* **PHPUnit feature tests** covering all API endpoints with database isolation via `RefreshDatabase`
* **Behat BDD API tests** with Gherkin scenarios and a Guzzle-based HTTP context
* **Playwright E2E tests** (native specs + Playwright-BDD) simulating real browser flows
* **Allure reporting** unified across PHPUnit, Behat, and Playwright into a single HTML report
* **Structured file logging** for every test framework, written to `storage/logs/`
* **Docker** containerization with MySQL, a 4-worker PHP server, and an Allure report server
* **CI/CD pipeline** via GitHub Actions with three jobs: PHPUnit, E2E, and report generation

## Tech Stack
* PHP 8.2 / Laravel 12
* React 19 + Vite 8
* Tailwind CSS 4
* Laravel Sanctum
* PHPUnit 11
* Behat 3.x
* Playwright 1.x (TypeScript) + playwright-bdd
* Allure (allure-phpunit + allure-playwright)
* MySQL (XAMPP / Docker)
* Docker + Docker Compose
* GitHub Actions

## Quick Start

### Local (XAMPP / native)
```bash
# 1. Clone repo
git clone <repo-url>
cd e2e-testing-platform

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Set up database (update .env with your DB credentials first)
php artisan migrate --seed

# 6. Start servers (two terminals)
php artisan serve
npm run dev
```

Visit: `http://127.0.0.1:8000`

Login with seeded admin account:
* **Email:** admin@test.com
* **Password:** password123

### Docker
```bash
# Build and run — sets up DB, runs all tests, serves Allure report
docker compose up --build

# Laravel app:   http://localhost:8000
# Allure report: http://localhost:8080
```

## Project Structure
```text
e2e-testing-platform/
│
├── .github/workflows/ci.yml
│
├── allure-report/                  # Generated HTML report
├── allure-results/                 # Raw Allure JSON output (all frameworks)
│
├── app/
│   ├── Http/
│   │   ├── Controllers/API/        # Auth, User, Order, Payment controllers
│   │   ├── Requests/               # Form request validation per domain
│   │   └── Resources/              # API resource transformers
│   └── Models/                     # User, Order, Payment
│
├── database/
│   ├── factories/                  # Model factories for testing
│   ├── migrations/                 # Database schema
│   └── seeders/                    # Dev data seeders
│
├── e2e/
│   ├── *.spec.ts                   # Playwright native specs
│   ├── features/                   # Gherkin .feature files (Playwright-BDD)
│   ├── steps/                      # Step definitions (common + auth)
│   └── support/
│       └── file-reporter.ts        # Custom Playwright file logger
│
├── resources/
│   └── js/
│       ├── api/                    # Axios instance + interceptors
│       ├── pages/                  # React pages (Auth, Users, Orders, Payments)
│       └── app.jsx                 # SPA entry point + routing
│
├── routes/
│   ├── api.php                     # API routes (Sanctum protected)
│   └── web.php                     # SPA catch-all route
│
├── storage/logs/
│   ├── phpunit/                    # Timestamped PHPUnit run logs
│   ├── behat/                      # Timestamped Behat run logs
│   ├── chromium/                   # Playwright spec logs
│   └── chromium-bdd/               # Playwright BDD logs
│
├── tests/
│   ├── Feature/                    # PHPUnit API feature tests
│   │   ├── AuthTest.php
│   │   ├── UserTest.php
│   │   ├── OrderTest.php
│   │   └── PaymentTest.php
│   └── Behat/
│       ├── features/               # Gherkin .feature files
│       └── bootstrap/              # ApiContext + FileLogFormatter
│
├── allure.config.php
├── behat.yml
├── docker-compose.yml
├── docker-entrypoint.sh
├── Dockerfile
├── playwright.config.ts
└── vite.config.js
```

## Running Tests

### PHPUnit (Backend feature tests)
```bash
# Run all tests
php artisan test

# Run a specific suite
php artisan test --filter=AuthTest
php artisan test --filter=UserTest
php artisan test --filter=OrderTest
php artisan test --filter=PaymentTest
```

### Behat (BDD API tests)
```bash
# Make sure php artisan serve is running first

# Run all Behat scenarios
./vendor/bin/behat --no-interaction

# Run a specific feature file
./vendor/bin/behat tests/Behat/features/auth.feature
```

### Playwright (E2E)
```bash
# Make sure php artisan serve is running first

# Run all E2E tests (native specs + BDD)
npm test

# Run native specs only
npm run test:specs

# Run BDD feature tests only
npm run test:bdd

# Run in headed mode (see the browser)
npx playwright test --headed

# Open interactive UI mode
npx playwright test --ui
```

### Allure Report
```bash
# Generate HTML report from allure-results/
npm run allure:generate

# Generate and open in browser
npm run allure:report

# Open previously generated report
npm run allure:open
```

## Test Coverage Summary
| Suite | Tests |
|---|---|
| PHPUnit — Auth | 5 |
| PHPUnit — Users | 5 |
| PHPUnit — Orders | 7 |
| PHPUnit — Payments | 6 |
| Behat — Auth | 5 |
| Behat — Users | 4 |
| Behat — Orders | 4 |
| Behat — Payments | 4 |
| Behat — Flows | 4 |
| Playwright — Auth | 5 |
| Playwright — Users | 4 |
| Playwright — Orders | 4 |
| Playwright — Payments | 3 |
| Playwright BDD — Auth | 5 |
| Playwright BDD — Users | 4 |
| Playwright BDD — Orders | 4 |
| Playwright BDD — Payments | 4 |
| Playwright BDD — Flows | 4 |
| **Total** | **81** |

## API Endpoints
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | No | Register new user |
| POST | `/api/auth/login` | No | Login |
| POST | `/api/auth/logout` | Yes | Logout |
| GET | `/api/auth/me` | Yes | Get profile |
| GET | `/api/users` | Yes | List users |
| GET | `/api/users/{id}` | Yes | Get user |
| PUT | `/api/users/{id}` | Yes | Update user |
| DELETE | `/api/users/{id}` | Yes | Delete user |
| GET | `/api/orders` | Yes | List own orders |
| POST | `/api/orders` | Yes | Create order |
| GET | `/api/orders/{id}` | Yes | Get order |
| PUT | `/api/orders/{id}` | Yes | Update order |
| DELETE | `/api/orders/{id}` | Yes | Delete order |
| GET | `/api/payments` | Yes | List own payments |
| POST | `/api/payments` | Yes | Process payment |
| GET | `/api/payments/{id}` | Yes | Get payment |

## Logging
Every test framework writes structured logs to `storage/logs/` using custom event-based extensions — no third-party logging libraries required.

| Framework | Implementation | Output |
|---|---|---|
| PHPUnit | `FileLoggerExtension` (PHPUnit 11 events) | `storage/logs/phpunit/test-run-{timestamp}.log` |
| Behat | `FileLogFormatter` + `FileLogExtension` | `storage/logs/behat/test-run-{timestamp}.log` |
| Playwright specs | `file-reporter.ts` (custom Reporter) | `storage/logs/chromium/test-run-{timestamp}.log` |
| Playwright BDD | same `file-reporter.ts` | `storage/logs/chromium-bdd/test-run-{timestamp}.log` |

Each log entry records the test/scenario name, status (PASSED / FAILED / SKIPPED), duration in milliseconds, and the first line of any error message. A summary is appended at the end of each run.

## Reporting (Allure)
All three frameworks feed into a single Allure report.

**PHPUnit** — uses `allure-framework/allure-phpunit`. Tests are annotated with:
```php
#[ParentSuite('PHPUnit')]
#[Suite(PaymentTest::class)]
#[SubSuite('Payments')]
#[DisplayName("User can process a payment for their order")]
```

**Playwright** — uses `allure-playwright`. Configured in `playwright.config.ts` with screenshot-on-failure and video-retain-on-failure.

**Behat** — results are written to `allure-results/` alongside PHPUnit and Playwright output.

The raw JSON files from all frameworks land in `allure-results/`. Run `npm run allure:generate` (or the CI report job) to merge them into a single `allure-report/index.html`.

## BDD
Two BDD layers cover the same four domains from different angles.

### Behat (API-level)
Scenarios are written in Gherkin and executed against the live API using `ApiContext` (Guzzle HTTP client). Feature files live in `tests/Behat/features/`.

```gherkin
Scenario: User can login
  When I POST to "/api/auth/login" with:
    | email    | admin@test.com |
    | password | password123    |
  Then the response status should be 200
  And the response should contain "token"
```

### Playwright-BDD (Browser-level)
Uses `playwright-bdd` to drive Chromium with the same Gherkin syntax. Feature files live in `e2e/features/`, step definitions in `e2e/steps/`.

```gherkin
Scenario: Authenticated user sees their orders
  Given I am logged in
  When I go to the orders page
  Then I should see the orders table
```

Run `npm test` to execute both the native Playwright specs and the BDD feature tests in a single pass.

## Docker
The Docker setup runs the full test suite inside a self-contained environment — no XAMPP or local PHP required.

**Services (`docker-compose.yml`):**
| Service | Image | Purpose |
|---|---|---|
| `mysql` | mysql:8.0 | Application database |
| `app` | Custom (Ubuntu 22.04) | PHP 8.2, Node 20, Chromium, all tests |

**What the entrypoint does (in order):**
1. Waits for MySQL to be healthy
2. Generates app key, runs migrations and seeders
3. Builds the React frontend (`npm run build`)
4. Starts the PHP dev server on port 8000 (4 workers)
5. Runs PHPUnit → Behat → Playwright sequentially
6. Generates the Allure report
7. Serves the report via a Python HTTP server on port 8080
8. Prints a formatted results summary to stdout

```bash
docker compose up --build
# Laravel: http://localhost:8000
# Allure:  http://localhost:8080
```

## CI/CD
GitHub Actions runs on every push to `main` / `development` and on pull requests to `main`.

### Jobs

**1. phpunit** (`ubuntu-latest`)
- PHP 8.2 + SQLite (in-memory, no MySQL service needed)
- Runs `php artisan test`
- Uploads `allure-results-phpunit` artifact (7-day retention)

**2. e2e** (`ubuntu-latest`)
- MySQL 8.0 service with health-check
- PHP 8.2 + Node 20 + Chromium
- Runs Behat, then Playwright (specs + BDD)
- Uploads `allure-results-e2e` artifact (7-day retention)
- Uploads `playwright-report` on failure (7-day retention)

**3. report** (depends on both, runs even if they fail)
- Downloads both allure artifact sets
- Merges and generates a unified `allure-report`
- Uploads `allure-report` artifact (14-day retention)

Workflow file: `.github/workflows/ci.yml`

## Troubleshooting

### PHP not found in terminal
```bash
source ~/.zshrc
php -v
```

### Storage permission errors
```bash
chmod -R 777 storage bootstrap/cache
```

### Database connection refused
Ensure XAMPP MySQL is running, then verify `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e2e_testing_platform
DB_USERNAME=root
DB_PASSWORD=
```

### Playwright tests failing
Ensure `php artisan serve` is running before executing Playwright tests. The base URL is `http://127.0.0.1:8000`.

### Behat tests failing
Ensure `php artisan serve` is running. Behat uses `http://127.0.0.1:8000` as its base URL (configured in `behat.yml`).

### Docker: port already in use
Stop any local PHP or MySQL processes before running `docker compose up`, as the container binds to ports 8000 and 8080.
