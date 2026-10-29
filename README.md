## Project Milestones

### Milestone 1: Initial Setup, Static Frontend and Database Planning

Milestone 1 established the initial project structure and implemented a responsive static frontend for the BookNest Library Management and Book Reservation System.

The frontend was developed using HTML, CSS, and vanilla JavaScript. It includes navigation between application pages and uses sample data to demonstrate the planned functionality.

**Implemented frontend pages:**
- Dashboard
- Book Catalog
- Book Details
- Reservations
- Loans
- Discover
- Profile
- Login
- Register
- Admin / Library Management

**Frontend functionality:**
- Browse and search for books.
- Filter books by category and availability.
- View book details.
- Demonstrate book reservation and cancellation using sample data.
- View loan information and due dates.
- Preview loan renewal functionality.
- Demonstrate adding a sample book through the library management interface.
- Navigate between pages using the application's navigation system.
- Display a responsive interface for different screen sizes.

**Database planning:**

The initial milestone included database planning and entity relationship documentation. The planned entities included Users, Books, Book Copies, Book Authors, Authors, Categories, Reservations, and Loans.

The initial frontend used sample data. Authentication, persistent storage, and full backend integration were not part of the initial static frontend implementation.

### Milestone 2: Database and DAO Layer

Milestone 2 extends the initial project by introducing a MySQL relational database and a PHP data access layer.

**Database:**
- Create the `booknest_db` MySQL database.
- Define entities, primary keys, and foreign key relationships.
- Include sample data for testing.
- Provide the SQL schema and sample data in `backend/database/booknest.sql`.

**DAO layer:**
- Implement `BaseDAO.php` for reusable database connection and query methods.
- Implement DAO classes for Users, Books, Authors, Categories, Loans, and Reservations.
- Use PDO and prepared statements for database operations.
- Provide Create, Read, Update, and Delete methods in the DAO layer.

**Configuration:**

Database connection settings are loaded from the backend configuration file. The actual `config.php` file must remain excluded from version control, while `config.example.php` is provided as a template.

**Project structure:**

The project separates frontend files, backend components, database scripts, configuration, and documentation into dedicated folders.

### Future Development

Future milestones may extend the application with additional backend routes, authentication, authorization, frontend-backend integration, and further library management functionality according to the project requirements.