# BookNest Backend

The REST backend uses FlightPHP 3, PHP 8.1+, PDO and MySQL.

## Local setup

1. Start Apache and MySQL in XAMPP.
2. Confirm the `booknest_db` database and six tables exist. For a fresh setup, import `database/booknest.sql`. It drops and recreates the six BookNest tables, so back up an existing database before import.
3. The ZIP handoff includes `vendor/`. If `vendor/autoload.php` is missing, run `composer install` from this directory.
4. Configure `config/config.php`. The supplied local file uses standard XAMPP defaults. The optional `google_books_api_key` setting enables Google Books as the primary provider; when blank/unavailable, Discover uses the Open Library fallback.
5. Open the backend through Apache. The API base endpoint is `/backend/api` relative to the project folder.

## Swagger

Open `docs/swagger.html` through Apache. The OpenAPI contract is `docs/openapi.yaml`.

## CRUD resources

Each resource supports `GET /api/{resource}`, `GET /api/{resource}/{id}`, `POST /api/{resource}`, `PUT /api/{resource}/{id}`, `PATCH /api/{resource}/{id}`, and `DELETE /api/{resource}/{id}`:

- `users`
- `books`
- `authors`
- `categories`
- `loans`
- `reservations`

Reservation lists can be filtered using `GET /api/reservations?user_id=3`.

## External book search

`GET /api/external/books?q=dervis%20i%20smrt&maxResults=12` searches Google Books when configured and automatically falls back to Open Library otherwise. The Google Books API key is read only on the server from `config/config.php`; it is never exposed to JavaScript. Both providers use a 4-second connection timeout and an 8-second overall timeout. Failures are logged to `logs/error.log` and return a friendly JSON response.

## Response format

Successful requests return `{ "success": true, "data": ... }`. Errors return `{ "success": false, "error": "..." }` with an HTTP status such as 400, 401, 403, 404, 500 or 503. JWT authentication and role middleware are part of Milestone 4.
