# AGENTS.md — BookNest project context

## Project
BookNest is a Library Management and Book Reservation System. It is a single-page web application with a PHP REST API and a MySQL database.

## Technology stack
- Frontend: HTML, CSS and vanilla JavaScript.
- Backend: PHP 8.1+ and FlightPHP 3.
- Database: MySQL through PDO and prepared statements.
- API documentation: OpenAPI 3.0 and Swagger UI.
- External integration: Google Books as the primary provider when configured, with Open Library as a fallback.

## Architecture rules
- Keep frontend and backend code separate.
- Follow the request flow: FlightPHP route → service → DAO → MySQL.
- Services contain validation and business rules. Services are the only application layer that calls DAO classes.
- Routes handle HTTP input/output and return JSON with appropriate status codes.
- DAO classes contain database queries and use prepared statements for values.
- Never return password hashes or API keys from an endpoint.
- Keep local credentials in `backend/config/config.php`; commit only `backend/config/config.example.php`.
- Do not commit runtime logs or `backend/vendor/`.
- External API calls must use bounded timeouts. Failures must be logged, return a friendly message, and not crash the frontend.

## Implemented entities
The implemented schema contains exactly six tables: `users`, `authors`, `categories`, `books`, `loans`, and `reservations`. Keep `docs/ERD.md`, `docs/ERD.svg`, the SQL file, DAOs, services and OpenAPI specification synchronized with this schema.

## Project structure
- `frontend/css/`: stylesheets.
- `frontend/js/`: SPA JavaScript.
- `frontend/views/`: HTML templates loaded by the SPA.
- `frontend/assets/`: static assets and sample data.
- `backend/routes/`: FlightPHP route registration.
- `backend/services/`: business logic and external API services.
- `backend/dao/`: PDO data-access objects.
- `backend/config/`: local configuration and committed examples.
- `backend/logs/`: runtime logs; never commit log files.
- `backend/docs/`: OpenAPI YAML and Swagger page.
- `backend/database/`: MySQL schema and sample data.
- `docs/`: ERD, milestone testing guide and AI log.

## Naming and coding conventions
- Use PascalCase for PHP class names and `camelCase` for methods/variables.
- Keep one service per database entity.
- Use descriptive validation messages and avoid exposing SQL errors, file paths or stack traces to API clients.
- Keep the OpenAPI specification synchronized with every route change.
- Preserve the existing six database entities.

## Local development
1. Start Apache and MySQL in XAMPP.
2. Configure `backend/config/config.php` using the example file if needed.
3. Confirm `backend/vendor/autoload.php` exists; run `composer install` in `backend/` if it does not.
4. Open the frontend through Apache, not with `file://`.
5. Open `backend/docs/swagger.html` to explore the API.
