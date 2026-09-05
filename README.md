# Inventra

Inventra is a modern Inventory & Asset Management System designed to streamline inventory tracking, asset lifecycle management, and related operations.

## Overview

Inventra is a web-based application built to solve the complexities of tracking inventory and assets across different departments and units. The main goal of the system is to provide a centralized, role-based platform where users can securely manage master data, monitor stock levels, and handle asset assignments efficiently. It is designed for internal use by company administrators, staff, and management.

## Key Features

### Implemented
* **Authentication:** Secure login and session management.
* **Role-Based Access Control (RBAC):** Granular permission and role management system.
* **Master Data Management:**
  * Company Profile Settings
  * Departments Management
  * Units Management
  * Categories Management
  * Suppliers Management

### Planned / In Progress
* Inventory Tracking and Management
* Asset Lifecycle Management
* Purchasing and Sales Modules
* Advanced Reporting and Analytics

## Tech Stack

### Backend
* Laravel 12.0
* PHP ^8.2

### Frontend
* Vue 3 (v3.4.0)
* Inertia.js (^2.0.0)
* Tailwind CSS (v3.2.1)
* Vite (v7.0.7)

### Database
* SQLite (Default)
* PostgreSQL 17 (via Docker)

### Infrastructure
* Docker & Docker Compose

### Development Tools
* Node.js & npm
* PHPUnit

## Architecture Overview

Inventra follows a modular monolith architecture built on top of Laravel and Inertia.js. It leverages standard MVC patterns in the backend and component-based UI in the frontend, communicating seamlessly without building a separate API layer.

For detailed architecture documentation, refer to the following:
* [`docs/architecture/SYSTEM_ARCHITECTURE.md`](docs/architecture/SYSTEM_ARCHITECTURE.md)
* [`docs/architecture/MODULE_ARCHITECTURE.md`](docs/architecture/MODULE_ARCHITECTURE.md)
* [`docs/architecture/DATA_FLOW.md`](docs/architecture/DATA_FLOW.md)
* [`docs/architecture/SECURITY_ARCHITECTURE.md`](docs/architecture/SECURITY_ARCHITECTURE.md)

## Project Structure

* `app/`: Contains the core PHP/Laravel backend logic (Controllers, Models, Middleware).
* `database/`: Holds database migrations, seeders, and SQLite database file.
* `docs/`: Comprehensive project documentation, including PRD, feature decisions, and architecture.
* `resources/`: Contains frontend assets, including Vue components (`resources/js/`) and CSS (`resources/css/`).
* `routes/`: Application routing definitions (`web.php`, `auth.php`).
* `tests/`: Automated tests (Feature and Unit tests).
* `public/`: The web root directory containing compiled assets and the entry point `index.php`.

## Requirements

* PHP 8.2 or higher
* Composer
* Node.js & npm
* SQLite or PostgreSQL
* Docker and Docker Compose (Optional, but recommended for database)
* Git

## Installation

### Using Docker (Recommended for Database)

1. Clone the repository and enter the directory:
   ```bash
   git clone <repository-url>
   cd laravel_Inventra_vue
   ```
2. Set up the environment configuration:
   ```bash
   cp .env.example .env
   ```
   *Update the database connection in `.env` to use PostgreSQL as defined in the project's Compose file:*
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5433
   DB_DATABASE=inventory_management
   DB_USERNAME=inventory_app
   DB_PASSWORD=inventory_app_password
   ```
3. Start the Docker containers (PostgreSQL database):
   ```bash
   docker compose up -d
   ```
4. Install PHP dependencies:
   ```bash
   composer install
   ```
5. Install Node.js dependencies:
   ```bash
   npm install
   ```
6. Generate the application key:
   ```bash
   php artisan key:generate
   ```
7. Run database migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```
8. Build frontend assets:
   ```bash
   npm run build
   ```

### Local Development (SQLite)

If you prefer to use the default SQLite database:
1. Ensure `DB_CONNECTION=sqlite` is set in your `.env`.
2. Follow steps 1, 2, 4, 5, 6, and 8 from above.
3. Create the SQLite database:
   ```bash
   touch database/database.sqlite
   ```
4. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

## Environment Configuration

The application requires environment variables to run. Copy the `.env.example` file to create your own `.env` file.
* Do not commit your `.env` file to version control.
* Update `APP_URL`, `DB_*` variables, and other configuration settings according to your local environment.

## Database Setup

Inventra uses standard Laravel migrations and seeders.
* **Migrations**: Define the schema for users, roles, permissions, master data, and company profiles.
* **Seeders**: Pre-populate the database with essential roles, permissions, and initial master data (e.g., `RoleSeeder`, `PermissionSeeder`).

To refresh and seed the database during development:
```bash
php artisan migrate:fresh --seed
```

## Running the Application

1. Start the backend server:
   ```bash
   php artisan serve
   ```
2. In a separate terminal window, start the frontend development server:
   ```bash
   npm run dev
   ```
3. Access the application at `http://localhost:8000`.

## Testing

The project includes PHPUnit for automated testing, with test cases organized in the `tests/` directory.

To run the test suite:
```bash
php artisan test
```
* Includes **Feature tests** for application features.
* Includes **Unit tests** for specific components.

## Development Workflow

1. Understand the requirement.
2. Inspect the existing implementation.
3. Understand the architecture using the `docs/` folder.
4. Plan changes.
5. Implement the feature or fix.
6. Test using PHPUnit and manual testing.
7. Review the code.
8. Update related documentation in the `docs/` folder.

## Documentation

### Product
* [Product Requirements Document (PRD)](docs/00_PRD.md)
* [Product Vision](docs/01_PRODUCT_VISION.md)
* [Feature Decisions](docs/02_FEATURE_DECISIONS.md)
* [Modules](docs/03_MODULES.md)
* [User Flow](docs/04_USER_FLOW.md)

### Architecture & Database
* [Architecture: System](docs/architecture/SYSTEM_ARCHITECTURE.md)
* [Architecture: Modules](docs/architecture/MODULE_ARCHITECTURE.md)
* [Architecture: Data Flow](docs/architecture/DATA_FLOW.md)
* [Architecture: Security](docs/architecture/SECURITY_ARCHITECTURE.md)
* [Database Schema](docs/05_DATABASE.md)
* [API Documentation](docs/06_API.md)

### Permissions & Development
* [Permission Matrix](docs/07_PERMISSION_MATRIX.md)
* [Roadmap](docs/08_ROADMAP.md)
* [Backlog](docs/09_BACKLOG.md)
* [Code Documentation Standard](docs/10_CODE_DOCUMENTATION_STANDARD.md)
* [Development Sprints](docs/LISTSPRINT.md)

## Security

* The `.env` file must not be committed to the repository.
* No credentials or secrets should be hardcoded in the codebase.
* Authentication and authorization (RBAC) must be respected for all protected routes.
* Input must be validated using Laravel Form Requests.
* Dependencies must be kept updated to avoid known vulnerabilities.
* Security-sensitive changes must undergo code review.

## Contribution

For contributions, please follow the established development workflow and consult the documentation in `docs/10_CODE_DOCUMENTATION_STANDARD.md`. Maintain the modular architecture and adhere to Laravel and Vue.js best practices.

## License

No explicit license has been determined for this project yet.
