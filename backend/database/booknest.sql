) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `authors` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `biography` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `books` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `isbn` VARCHAR(20) DEFAULT NULL,
    `publication_year` INT DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `total_copies` INT NOT NULL DEFAULT 1,
    `available_copies` INT NOT NULL DEFAULT 1,
    `category_id` INT DEFAULT NULL,
    `author_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_book_isbn` (`isbn`),
    KEY `idx_books_category_id` (`category_id`),
    KEY `idx_books_author_id` (`author_id`),
    CONSTRAINT `fk_books_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_books_author`
        FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_books_total_copies` CHECK (`total_copies` >= 0),
    CONSTRAINT `chk_books_available_copies` CHECK (`available_copies` >= 0 AND `available_copies` <= `total_copies`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loans` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `book_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `borrowed_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `returned_date` DATE DEFAULT NULL,
    `status` ENUM('Borrowed', 'Returned', 'Overdue') NOT NULL DEFAULT 'Borrowed',
    PRIMARY KEY (`id`),
    KEY `idx_loans_book_id` (`book_id`),
    KEY `idx_loans_user_id` (`user_id`),
    CONSTRAINT `fk_loans_book`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_loans_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_loans_dates` CHECK (`due_date` >= `borrowed_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `reservation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Queued', 'Ready', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Queued',
    PRIMARY KEY (`id`),
    KEY `idx_reservations_user_id` (`user_id`),
    KEY `idx_reservations_book_id` (`book_id`),
    CONSTRAINT `fk_reservations_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reservations_book`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample users.
-- These are demo accounts only. Password hash below is PHP's documented
-- example bcrypt hash for the password "password"; replace for real accounts.
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`) VALUES
(3, 'Kanita Plasto', 'kanita@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
(4, 'BookNest Librarian', 'librarian@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian');

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(4, 'Fantasy', 'Fantasy and magical stories'),
(5, 'Classic Literature', 'Classic literary works'),
(6, 'Mystery', 'Mystery and detective fiction'),
(7, 'Science Fiction', 'Science fiction and speculative stories');

INSERT INTO `authors` (`id`, `first_name`, `last_name`, `biography`) VALUES
(4, 'J. R. R.', 'Tolkien', 'English writer known for fantasy literature'),
(5, 'Ivo', 'Andrić', 'Nobel Prize-winning writer from Bosnia and Herzegovina'),
(6, 'Agatha', 'Christie', 'British mystery novelist'),
(7, 'George', 'Orwell', 'English novelist and essayist'),
(8, 'Frank', 'Herbert', 'American science fiction author'),
(9, 'Meša', 'Selimović', 'Bosnian novelist'),
(10, 'Jane', 'Austen', 'English novelist'),
(11, 'Isaac', 'Asimov', 'Science fiction author');

INSERT INTO `books`
(`id`, `title`, `isbn`, `publication_year`, `description`, `total_copies`, `available_copies`, `category_id`, `author_id`) VALUES
(15, 'Derviš i smrt', '9789958614001', 1966, 'Roman Meše Selimovića', 3, 2, 5, 9),
(16, 'The Hobbit', '9780547928227', 1937, 'A fantasy adventure by J. R. R. Tolkien', 4, 3, 4, 4),
(17, 'Murder on the Orient Express', '9780062693662', 1934, 'A Hercule Poirot mystery', 2, 2, 6, 6),
(18, '1984', '9780451524935', 1949, 'A dystopian novel by George Orwell', 3, 3, 7, 7),
(19, 'Dune', '9780441172719', 1965, 'A science fiction novel by Frank Herbert', 2, 2, 7, 8),
(20, 'Pride and Prejudice', '9780141439518', 1813, 'A classic novel by Jane Austen', 2, 2, 5, 10),
(21, 'Foundation', '9780553293357', 1951, 'A science fiction novel by Isaac Asimov', 2, 2, 7, 11),
(22, 'The Fellowship of the Ring', '9780547928210', 1954, 'The first volume of The Lord of the Rings', 3, 3, 4, 4);

-- Example loan (due date is 14 days after borrowed date).
INSERT INTO `loans`
(`id`, `book_id`, `user_id`, `borrowed_date`, `due_date`, `returned_date`, `status`) VALUES
(1, 16, 3, '2026-10-16', '2026-10-30', NULL, 'Borrowed');

-- Example reservation: ID 2 matches the test reservation discussed during development.
INSERT INTO `reservations`
(`id`, `user_id`, `book_id`, `reservation_date`, `status`) VALUES
(2, 3, 15, '2026-10-29 12:00:00', 'Cancelled');

-- Reset AUTO_INCREMENT values above the inserted IDs.
ALTER TABLE `users` AUTO_INCREMENT = 5;
ALTER TABLE `categories` AUTO_INCREMENT = 8;
ALTER TABLE `authors` AUTO_INCREMENT = 12;