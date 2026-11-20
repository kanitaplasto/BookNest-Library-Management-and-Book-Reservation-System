# AI Usage Log

## Milestone 1 — Initial Setup, Static Frontend and Database Planning

### AI tools and tasks
- Tool: ChatGPT.
- Used to interpret the assignment, plan the BookNest folder structure, draft and format static frontend page content, and discuss the initial entity-relationship design.

### Main prompt summary
- “Help me prepare the BookNest project according to the Milestone 1 instructions and explain where the content belongs in the provided template.”

### Review and corrections
- The original ERD was a planning draft with eight conceptual entities. During the later database implementation, the actual schema was simplified to six tables. The ERD Markdown and SVG were updated to describe the implemented schema so that the database, code and diagram agree.

## Milestone 2 — Database and DAO Layer

### AI tools and tasks
- Tool: ChatGPT.
- Used to discuss the MySQL schema, sample data, DAO responsibilities, prepared statements and PHP classes for the six implemented entities.

### Main prompt summary
- “Help implement the BookNest database and DAO layer for users, books, authors, categories, loans and reservations, while preserving the existing project structure.”

### Review and corrections
- A review of the newest uploaded project archive found that its SQL file was structurally complete but its sample book data was an older dataset (eight international titles, IDs 15–22). The student's later live API response and phpMyAdmin screenshot showed ten regional titles (IDs 15–24), different ISBNs, copy counts and foreign-key IDs. The SQL seed was updated to match the latest observed book records and the corresponding author/category ID references.
- The SQL file remains a fresh-install script that drops and recreates the six BookNest tables. Back up an existing database before importing it.

## Milestone 3 — Business Logic, Presentation Layer, OpenAPI and External API

### AI tools and tasks
- Tool: ChatGPT (GPT-6).
- Used to review the DAO/database structure, services and validation, FlightPHP CRUD routes, OpenAPI/Swagger documentation, and the external book search feature.

### Main prompt summary
- “Continue BookNest from Milestone 2 and implement services and validation for each entity, FlightPHP CRUD routes for all entities, OpenAPI/Swagger documentation and an external book search feature.”

### Two AI mistakes/weak points found during review and corrected
1. **The earlier ERD did not match the implemented schema.** It still contained `BOOK_COPIES` and `BOOK_AUTHORS`, which are not separate tables in the current SQL database. The ERD Markdown and SVG were revised to the actual six tables and their real foreign-key relationships.
2. **The external search depended entirely on a Google Books API key that was not present in the local config.** This meant the Discover feature could not return results in a fresh local setup. The service was changed to use Google Books when a key is configured and to fall back to the public Open Library Search API if the key is empty or Google Books is unavailable. The API key remains in local configuration and is never exposed to the frontend.

### Validation performed for this handoff
- PHP syntax checks (`php -l`) on backend PHP files.
- JavaScript syntax check on `frontend/js/app.js`.
- JSON parsing of `backend/composer.json` and `frontend/assets/books.json`.
- YAML parsing of `backend/docs/openapi.yaml` and validation of local component references.
- Static comparison of all six CRUD route resources against the OpenAPI paths.
- Confirmed the SQL sample records use the latest observed live book IDs, ISBNs, copy counts and author/category references.
- Live Books API CRUD testing was performed by the student in XAMPP/Swagger during development. Other resource routes and the external providers still require local live testing before submission.

### Student review reminder
AI-assisted code must be read, tested and understood by the student. Update this log if additional changes are made, and be prepared to explain each layer during the project defense.
