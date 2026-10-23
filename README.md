# BookNest — Library Management and Book Reservation System

BookNest helps library members discover titles, preview reservations and track loans. Librarians will manage catalog entries and circulation in later milestones.

## Milestone 1 scope

Static responsive single-page application built with HTML, CSS and vanilla JavaScript. Hash navigation loads HTML templates into a shared layout without full page reloads. All data is local sample data; no backend, authentication, persistent storage or real reservations are implemented.

## Run locally

From the project root, with Python 3 installed:

```bash
python -m http.server 8000 --directory frontend
```

On Windows, use `py` instead of `python` if necessary. Open **http://localhost:8000**. Alternatively, open `frontend/index.html` with VS Code Live Server. Do not double-click index.html: template fetches require HTTP. No npm install or external CDN is required.

## Pages and components

| Route | Purpose |
| --- | --- |
| `#dashboard` | Overview, sample reading statistics, recommended books |
| `#catalog` | Search by title/author; category and availability filters |
| `#book/1` | Book details and in-memory reservation preview |
| `#reservations` | Sample and newly added reservations; cancellation preview |
| `#loans` | Sample loan, due date and renewal preview |
| `#discover` | Planned external API feature with local search preview |
| `#profile` | Member information form and membership summary |
| `#login` | Sign-in form and validation preview |
| `#register` | Registration and matching-password validation |
| `#admin` | Librarian inventory, add-book form and circulation plan |

Shared components: sidebar navigation, header, footer, book cards, status badges, forms, tables, empty states, notifications and responsive layouts. All screens are accessible for evaluation; librarian restrictions will be implemented later. Demo changes reset on page reload; form passwords are never stored or sent.

## Project structure

```text
BookNest/
  frontend/
    index.html
    css/style.css
    js/app.js
    views/                HTML page templates
    assets/               Local logo and sample book data
  backend/
    routes/               Future HTTP endpoints
    services/             Business logic
    dao/                  Data access layer
    middleware/           Authentication and request validation
    config/               Safe example environment configuration
    logs/                 Runtime logs ignored by Git
  docs/
    ERD.md                Detailed schema, relationships and rules
    ERD.svg               Standalone draft diagram
    SUBMISSION.md         Submission and demonstration checklist
  .gitignore
  README.md
```

Empty backend directories contain `.gitkeep` so Git preserves the structure. Runtime logs, vendor dependencies, local environment secrets and `backend/config/config.php` are ignored. Only a non-secret example configuration is included. Backend implementation is intentionally deferred.

## Database planning

Eight planned entities: Users, Books, Book Copies, Authors, Book Authors, Categories, Reservations and Loans. See [detailed ERD](docs/ERD.md) and [visual ERD](docs/ERD.svg). Separating titles and copies supports multiple physical copies and historical loans; the author junction supports many-to-many authorship.

## External API plan

The Discover page will integrate Open Library Search API to search external titles and display author, publication year and cover information. Current results are local fixtures. Future implementation must verify the provider documentation, add loading/error/empty states, and handle unavailable covers and rate limits. No API calls are made in Milestone 1.

## Manual verification

1. Start the server; visit every navigation link and use browser Back/Forward. The page must not fully reload.
2. Search `Atomic`; filter by Classics and by Available; test a no-match search.
3. Open an available book, reserve it, verify My reservations, cancel it, and check the overview count. Repeated active reservations and more than three are prevented.
4. Open an unavailable book; the reservation control must be disabled.
5. Test empty/invalid emails, short passwords and mismatched confirmation. Submit valid demo forms; no real account is created.
6. Preview loan renewal and add a sample book from Library management; verify it appears in the catalog. Reload to reset demo changes.
7. Check layout at mobile and desktop widths; verify keyboard navigation and visible focus.
8. Open both ERD files and check that all eight entities and relationships are included.

## Submission

Topic: **BookNest — Library Management and Book Reservation System**.

Upload the project contents to your own GitHub repository, verify that all required directories and documents are visible, and submit the topic and repository link through the LMS form. Deadline supplied in the assignment: Sunday 25 October 2026, latest Monday 26 October 2026 at 08:00. This package does not create or submit a GitHub repository.
