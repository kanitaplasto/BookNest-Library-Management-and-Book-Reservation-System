<?php

// FlightPHP is installed in backend/vendor by running: composer install
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/services/ApiException.php';
require_once __DIR__ . '/services/ApiLogger.php';
require_once __DIR__ . '/routes/ApiRoutes.php';

Flight::set('flight.handle_errors', true);
Flight::set('flight.log_errors', true);

Flight::map('notFound', function (): void {
    Flight::json([
        'success' => false,
        'error' => 'The requested endpoint was not found.'
    ], 404);
});

Flight::map('error', function (Throwable $exception): void {
    ApiLogger::error('Unhandled FlightPHP error: ' . $exception->getMessage());
    Flight::json([
        'success' => false,
        'error' => 'An unexpected server error occurred.'
    ], 500);
});

ApiRoutes::register();
Flight::start();
