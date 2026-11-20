<?php

return [
    'host' => '127.0.0.1',
    'database' => 'booknest_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    // Optional: when provided, Google Books is the primary search provider.
    // If empty or unavailable, BookNest uses the Open Library Search API.
    'google_books_api_key' => '',
    // Identify the application when making public Open Library requests.
    'open_library_user_agent' => 'BookNestLibrary/1.0 (student project)'
];
