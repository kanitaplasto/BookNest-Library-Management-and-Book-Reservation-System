# BookNest — Library Management and Book Reservation System

BookNest is a single-page library application with an HTML/CSS/JavaScript frontend, a PHP REST backend built with FlightPHP, and a MySQL database accessed through PDO and prepared statements.

## Project structure

- `frontend/` — SPA entry page, page templates, CSS, JavaScript and static demo assets.
- `backend/routes/` — FlightPHP REST endpoint registration.
- `backend/services/` — business logic, validation and the external book-search integration.
- `backend/dao/` — PDO data-access classes for the six database entities.
- `backend/config/` — local database/API configuration and a safe example file.
- `backend/database/booknest.sql` — complete schema and demonstration data.
- `backend/docs/` — OpenAPI specification and Swagger UI page.
- `docs/` — ERD, milestone testing guide and AI usage log.

## Implemented database entities

The current MySQL schema contains six tables: `users`, `authors`, `categories`, `books`, `loans`, and `reservations`. The diagram in `docs/ERD.md` and `docs/ERD.svg` matches those six implemented tables.

- Users can have many loans and reservations.
- Books can appear in many loans and reservations.
- Authors and categories can each be associated with many books.
- Each loan references one user and one book.
- Each reservation references one user and one book.

## Milestone 1 — Initial setup and static frontend

The project includes a responsive SPA with Dashboard, Catalog, Book Details, Reservations, Loans, Discover, Profile, Login, Register and Library Management pages. The Milestone 1 frontend uses sample data to demonstrate the layout and navigation; full frontend-to-backend connection is a later milestone requirement.

## Milestone 2 — Database and DAO layer

The MySQL schema defines primary keys, foreign keys, unique constraints and sample data for all six entities. `BaseDAO.php` provides the shared PDO connection/query helpers, and each entity has a DAO class with prepared-statement CRUD operations. Passwords are hashed before they are inserted or updated.

## Milestone 3 — Business logic, REST API, OpenAPI and external API

- Six service classes validate input and apply entity-specific rules.
- FlightPHP exposes GET (collection and by ID), POST, PUT, PATCH and DELETE endpoints for all six entities.
- The API returns JSON and appropriate success/error HTTP status codes.
- `backend/docs/openapi.yaml` documents the endpoints; `backend/docs/swagger.html` provides the Swagger UI.
- Discover searches public book metadata through the BookNest backend. Google Books is used when an API key is configured; Open Library is automatically used if the key is empty or Google Books is unavailable. Both providers use bounded connection/overall timeouts and logged error handling.

## Roles and local demonstration accounts

The database supports two roles: `member` and `librarian`.

| Role | Email | Sample password |
|---|---|---|
| Member | `kanita@example.com` | `password` |
| Librarian | `librarian@example.com` | `password` |

These are local demonstration credentials only. Do not reuse them outside a demo. JWT authentication and role-based route protection are part of Milestone 4, not Milestone 3.

## Run locally with XAMPP

1. Extract the project folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open phpMyAdmin and confirm that `booknest_db` exists. If creating a fresh database, import `backend/database/booknest.sql`.
   - **Warning:** the SQL script drops and recreates the six BookNest tables. Back up your current database before importing it.
   - The sample book records (IDs 15–24, ISBNs and foreign-key IDs) are aligned with the last BookNest API/database snapshot shared during development. The test book with ID 25 is intentionally not included because it was deleted during CRUD testing.
4. Open `backend/config/config.php`. The supplied file uses standard local XAMPP defaults (`127.0.0.1`, database `booknest_db`, username `root`, empty password). Change these values only if your MySQL setup differs.
5. The optional `google_books_api_key` setting can contain a Google Books API key. When it is empty, Discover uses Open Library as a no-key fallback. Never publish a real API key or commit `config.php`.
6. The ZIP handoff includes `backend/vendor/` so the project can start without a fresh Composer download. If `vendor/autoload.php` is missing, open a terminal in `backend/` and run `composer install`.
7. Open the frontend through Apache (not by double-clicking the HTML file):

   `http://localhost/BookNest-Library-Management-and-Book-Reservation-System/frontend/`

8. Open the REST API base endpoint:

   `http://localhost/BookNest-Library-Management-and-Book-Reservation-System/backend/api`

9. Open the Swagger documentation:

   `http://localhost/BookNest-Library-Management-and-Book-Reservation-System/backend/docs/swagger.html`

If your project folder has a different name, replace the folder segment in the URLs.

## Test the external book search

- Open the frontend **Discover** page and search for a title, author or ISBN.
- Or call `GET /api/external/books?q=dervis%20i%20smrt&maxResults=12` through Swagger.
- If a Google Books API key is present, Google Books is tried first. If it is missing or unavailable, the service falls back to Open Library. If both are unavailable, the API returns a friendly `503` JSON response and records the provider failure in `backend/logs/error.log`.

## Project status and upcoming milestones

M1, M2 and M3 are implemented in the project files. The Books CRUD endpoints were also tested live in Swagger during development (GET list, GET by ID, POST, PUT, PATCH and DELETE). Other entity endpoints still need individual live testing on the local machine. Authentication/JWT, authorization middleware, complete request logging and fully connected frontend CRUD workflows are scheduled for Milestone 4. Frontend MVC and deployment are scheduled for Milestone 5.

## AI usage and project review

`AGENTS.md` documents the project architecture and coding rules. `docs/AI_LOG.md` records AI-assisted work, corrections and validation performed. Review the code and testing guide before submission so you can explain the architecture and implementation.

## Deployment

The application is not deployed yet. Add the public deployment URL here after hosting is configured in a later milestone.
