CREATE DATABASE IF NOT EXISTS `booknest_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `booknest_db`;
SET NAMES utf8mb4;

-- BookNest - Library Management and Book Reservation System
-- Milestones 2 and 3: schema plus demonstration data.
-- IMPORTANT: This is a fresh-install script. It drops and recreates the six
-- BookNest tables. Back up an existing database before importing this file.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `loans`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('member', 'librarian') NOT NULL DEFAULT 'member',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_email` (`email`)
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
    CONSTRAINT `chk_books_available_copies`
        CHECK (`available_copies` >= 0 AND `available_copies` <= `total_copies`)
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

-- Demonstration accounts. The hash is for the sample password "password".
-- Replace these credentials before using the application outside a local demo.
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`) VALUES
(3, 'Kanita Plasto', 'kanita@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
(4, 'BookNest Librarian', 'librarian@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian');

-- Category IDs match the values observed in the running BookNest database.
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(4, 'Bosnian Literature', 'Literary works by Bosnian authors and regional classics.'),
(5, 'Croatian Literature', 'Literary works by Croatian authors.'),
(6, 'Serbian Literature', 'Literary works by Serbian authors.'),
(7, 'Regional Classics', 'Regional classics and well-known works for younger readers.');

-- Author IDs match the author_id values in the observed books API response.
INSERT INTO `authors` (`id`, `first_name`, `last_name`, `biography`) VALUES
(4, 'Meša', 'Selimović', 'Bosnian novelist, author of Derviš i smrt and Tvrđava.'),
(5, 'Ivo', 'Andrić', 'Nobel Prize-winning author of Na Drini ćuprija and Prokleta avlija.'),
(6, 'Branko', 'Ćopić', 'Bosnian and Yugoslav writer known for prose and literature for younger readers.'),
(7, 'Miroslav', 'Krleža', 'Croatian writer, playwright and essayist.'),
(8, 'Ante', 'Kovačić', 'Croatian novelist and writer.'),
(9, 'Miloš', 'Crnjanski', 'Serbian novelist, poet and essayist.'),
(10, 'Dobrica', 'Ćosić', 'Serbian novelist and essayist.'),
(11, 'Milorad', 'Pavić', 'Serbian writer and literary scholar, author of Hazarski rečnik.');

-- The ten title records below mirror the last observed live GET /api/books
-- response (IDs 15-24), including ISBNs, author/category references and copy counts.
INSERT INTO `books`
(`id`, `title`, `isbn`, `publication_year`, `description`, `total_copies`, `available_copies`, `category_id`, `author_id`, `created_at`) VALUES
(15, 'Derviš i smrt', '9789958200001', 1966, 'Roman Meše Selimovića o vjeri, pravdi, vlasti i ljudskoj prirodi.', 5, 5, 4, 4, '2026-10-29 19:28:42'),
(16, 'Na Drini ćuprija', '9789958200002', 1945, 'Historijski roman Ive Andrića o mostu u Višegradu i životima ljudi kroz različite epohe.', 4, 4, 4, 5, '2026-10-29 19:28:42'),
(17, 'Tvrđava', '9789958200003', 1970, 'Roman Meše Selimovića o ljubavi, društvu i ljudskoj odgovornosti.', 3, 3, 4, 4, '2026-10-29 19:28:42'),
(18, 'Ježeva kućica', '9789958200004', 1949, 'Poznato djelo Branka Ćopića o domu i pripadnosti.', 5, 5, 7, 6, '2026-10-29 19:28:42'),
(19, 'Prokleta avlija', '9789958200005', 1954, 'Djelo Ive Andrića koje istražuje vlast, zatočeništvo i ljudsku prirodu.', 3, 3, 4, 5, '2026-10-29 19:28:42'),
(20, 'Gospoda Glembajevi', '9789958200006', 1929, 'Drama Miroslava Krleže o porodici, bogatstvu i društvenom licemjerju.', 2, 2, 5, 7, '2026-10-29 19:28:42'),
(21, 'U registraturi', '9789958200007', 1888, 'Roman Ante Kovačića o obrazovanju, ambiciji i društvenim prilikama.', 3, 3, 5, 8, '2026-10-29 19:28:42'),
(22, 'Seobe', '9789958200008', 1929, 'Roman Miloša Crnjanskog o migracijama, identitetu i istoriji.', 4, 4, 6, 9, '2026-10-29 19:28:42'),
(23, 'Koreni', '9789958200009', 1954, 'Roman Dobrice Ćosića o porodici, tradiciji i sukobima u društvu.', 3, 3, 6, 10, '2026-10-29 19:28:42'),
(24, 'Hazarski rečnik', '9789958200010', 1984, 'Postmoderni roman Milorada Pavića predstavljen u obliku rječnika.', 2, 2, 6, 11, '2026-10-29 19:28:42');

-- Sample loan and reservation records for local API demonstrations.
INSERT INTO `loans`
(`id`, `book_id`, `user_id`, `borrowed_date`, `due_date`, `returned_date`, `status`) VALUES
(1, 16, 3, '2026-10-16', '2026-10-30', NULL, 'Overdue');

INSERT INTO `reservations`
(`id`, `user_id`, `book_id`, `reservation_date`, `status`) VALUES
(2, 3, 15, '2026-10-29 12:00:00', 'Cancelled');

-- Preserve the next available IDs from this seed dataset.
ALTER TABLE `users` AUTO_INCREMENT = 5;
ALTER TABLE `categories` AUTO_INCREMENT = 8;
ALTER TABLE `authors` AUTO_INCREMENT = 12;
ALTER TABLE `books` AUTO_INCREMENT = 25;
ALTER TABLE `loans` AUTO_INCREMENT = 2;
ALTER TABLE `reservations` AUTO_INCREMENT = 3;
