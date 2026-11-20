# Milestone 3 — Local Testing Guide

## 1. Start the project

1. Extract the project into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Confirm that `booknest_db` exists and contains the six tables: `users`, `authors`, `categories`, `books`, `loans`, and `reservations`.
4. Confirm `backend/config/config.php` contains the correct local database credentials. A Google Books API key is optional because Open Library is used as a fallback.
5. Confirm `backend/vendor/autoload.php` exists. If it does not, run `composer install` from `backend/`.
6. Open `backend/docs/swagger.html` through Apache.

## 2. Confirm that FlightPHP responds

Open `GET /api`. It should return a JSON message for the BookNest REST API.

## 3. Test the six CRUD resources

| Resource | Collection endpoint | Single-record endpoint |
|---|---|---|
| Users | `/api/users` | `/api/users/{id}` |
| Books | `/api/books` | `/api/books/{id}` |
| Authors | `/api/authors` | `/api/authors/{id}` |
| Categories | `/api/categories` | `/api/categories/{id}` |
| Loans | `/api/loans` | `/api/loans/{id}` |
| Reservations | `/api/reservations` | `/api/reservations/{id}` |

For every resource, test GET collection, GET by ID, POST, PUT, PATCH and DELETE. Expected successful status codes are 200 for reads/updates/deletes and 201 for creation.

### Example bodies

Create an author:

```json
{
  "first_name": "Test",
  "last_name": "Author",
  "biography": "Created while testing the BookNest API."
}
```

Create a category:

```json
{
  "name": "Test Category",
  "description": "Created while testing the BookNest API."
}
```

Create a book (use existing author/category IDs):

```json
{
  "title": "Test Book",
  "isbn": "9780000000001",
  "publication_year": 2024,
  "description": "Created while testing the BookNest API.",
  "total_copies": 2,
  "available_copies": 2,
  "author_id": 4,
  "category_id": 4
}
```

Create a loan (use an existing user and book, and valid dates):

```json
{
  "book_id": 16,
  "user_id": 3,
  "borrowed_date": "2026-11-20",
  "due_date": "2026-12-04",
  "returned_date": null,
  "status": "Borrowed"
}
```

Create a reservation:

```json
{
  "user_id": 3,
  "book_id": 15
}
```

Use throwaway test records and delete them after testing. Do not reuse a test ISBN that already exists.

## 4. Test validation and not-found responses

- Send a book with `available_copies` greater than `total_copies`; expect 400.
- Send a category with an empty name; expect 400.
- Request `/api/books/999999`; expect 404 if that ID does not exist.
- Create a duplicate ISBN; expect 400.
- Create more than three active reservations for the same user; the next one should be rejected with 400.

## 5. Test the external book search

Use:

```text
GET /api/external/books?q=dervis%20i%20smrt&maxResults=12
```

Or open the frontend **Discover** page and search by title, author or ISBN.

- With an API key in `config.php`, Google Books is used first.
- With an empty key, or when Google Books is unavailable, Open Library is used as a fallback.
- If both external providers fail, the endpoint returns a friendly 503 JSON response and the failure is recorded in `backend/logs/error.log`.

## 6. Authentication status codes

The OpenAPI documentation describes 401 and 403 for protected endpoints. JWT authentication and role middleware are introduced in Milestone 4; they are not implemented by the Milestone 3 CRUD routes yet.

## 7. Before submission

- Confirm Swagger loads and the paths match the route definitions.
- Remove throwaway test records from the database.
- Keep `config.php`, `vendor/` and runtime log files out of Git.
- Review `AGENTS.md` and `docs/AI_LOG.md`, and ensure you understand the code and can explain the architecture.
