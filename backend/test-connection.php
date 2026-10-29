<?php

require_once __DIR__ . '/dao/BaseDAO.php';

try {
    $database = new BaseDAO();

    echo "Database connection successful!";
} catch (Throwable $exception) {
    echo "Database connection failed: " . $exception->getMessage();
}