<?php

require_once __DIR__ . '/../services/ApiException.php';
require_once __DIR__ . '/../services/ApiLogger.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/BookService.php';
require_once __DIR__ . '/../services/AuthorService.php';
require_once __DIR__ . '/../services/CategoryService.php';
require_once __DIR__ . '/../services/LoanService.php';
require_once __DIR__ . '/../services/ReservationService.php';
require_once __DIR__ . '/../services/GoogleBooksService.php';

/** Converts every API exception into a predictable JSON response. */
function bookNestApiResponse(callable $callback, int $successStatus = 200): void
{
    try {
        $result = $callback();
        Flight::json([
            'success' => true,
            'data' => $result
        ], $successStatus);
    } catch (ApiException $exception) {
        Flight::json([
            'success' => false,
            'error' => $exception->getMessage()
        ], $exception->getStatusCode());
    } catch (InvalidArgumentException $exception) {
        Flight::json([
            'success' => false,
            'error' => $exception->getMessage()
        ], 400);
    } catch (PDOException $exception) {
        ApiLogger::error('Database error: ' . $exception->getMessage());
        $isConstraintError = str_starts_with((string) $exception->getCode(), '23');
        Flight::json([
            'success' => false,
            'error' => $isConstraintError
                ? 'The request conflicts with existing data or a database relationship.'
                : 'A database error occurred. Please try again later.'
        ], $isConstraintError ? 400 : 500);
    } catch (Throwable $exception) {
        ApiLogger::error('Unhandled API error: ' . $exception->getMessage());
        Flight::json([
            'success' => false,
            'error' => 'An unexpected server error occurred.'
        ], 500);
    }
}

/** Reads a JSON request body and rejects malformed or non-object JSON. */
function bookNestJsonBody(): array
{
    $rawBody = Flight::request()->getBody();

    try {
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new InvalidArgumentException('Request body must contain valid JSON.');
    }

    if (!is_array($data)) {
        throw new InvalidArgumentException('Request body must be a JSON object.');
    }

    return $data;
}

class ApiRoutes
{
    public static function register(): void
    {
        Flight::route('GET /api', function (): void {
            Flight::json([
                'success' => true,
                'message' => 'BookNest REST API',
                'documentation' => 'docs/swagger.html'
            ]);
        });

        self::registerCrud('users', UserService::class);
        self::registerCrud('books', BookService::class);
        self::registerCrud('authors', AuthorService::class);
        self::registerCrud('categories', CategoryService::class);
        self::registerCrud('loans', LoanService::class);
        self::registerCrud('reservations', ReservationService::class);

        Flight::route('GET /api/external/books', function (): void {
            bookNestApiResponse(function () {
                $query = Flight::request()->query->getData();
                $searchTerm = trim((string) ($query['q'] ?? ''));
                $maxResults = filter_var($query['maxResults'] ?? 12, FILTER_VALIDATE_INT);
                if ($maxResults === false) {
                    throw new InvalidArgumentException('maxResults must be an integer.');
                }

                $service = new GoogleBooksService();
                return $service->search($searchTerm, $maxResults);
            });
        });

        Flight::route('/api/*', function (): void {
            Flight::json([
                'success' => false,
                'error' => 'API endpoint not found.'
            ], 404);
        });
    }

    private static function registerCrud(string $resource, string $serviceClass): void
    {
        Flight::route('GET /api/' . $resource, function () use ($resource, $serviceClass): void {
            bookNestApiResponse(function () use ($resource, $serviceClass) {
                $service = new $serviceClass();
                if ($resource === 'reservations') {
                    $query = Flight::request()->query->getData();
                    if (isset($query['user_id'])) {
                        return $service->getByUserId($query['user_id']);
                    }
                }
                return $service->getAll();
            });
        });

        Flight::route('GET /api/' . $resource . '/@id', function ($id) use ($serviceClass, $resource): void {
            bookNestApiResponse(function () use ($id, $serviceClass, $resource) {
                $service = new $serviceClass();
                $record = $service->getById($id);
                if ($record === null) {
                    $labels = [
                        'users' => 'User',
                        'books' => 'Book',
                        'authors' => 'Author',
                        'categories' => 'Category',
                        'loans' => 'Loan',
                        'reservations' => 'Reservation'
                    ];
                    throw new ApiException(($labels[$resource] ?? 'Record') . ' not found.', 404);
                }
                return $record;
            });
        });

        Flight::route('POST /api/' . $resource, function () use ($serviceClass): void {
            bookNestApiResponse(function () use ($serviceClass) {
                $service = new $serviceClass();
                $id = $service->create(bookNestJsonBody());
                return [
                    'id' => (int) $id,
                    'record' => $service->getById($id)
                ];
            }, 201);
        });

        Flight::route('PUT /api/' . $resource . '/@id', function ($id) use ($serviceClass): void {
            bookNestApiResponse(function () use ($id, $serviceClass) {
                $service = new $serviceClass();
                return $service->update($id, bookNestJsonBody());
            });
        });

        Flight::route('PATCH /api/' . $resource . '/@id', function ($id) use ($serviceClass): void {
            bookNestApiResponse(function () use ($id, $serviceClass) {
                $service = new $serviceClass();
                return $service->update($id, bookNestJsonBody());
            });
        });

        Flight::route('DELETE /api/' . $resource . '/@id', function ($id) use ($serviceClass): void {
            bookNestApiResponse(function () use ($id, $serviceClass) {
                $service = new $serviceClass();
                $deleted = $service->delete($id);
                if (!$deleted) {
                    throw new ApiException('Record could not be deleted.', 404);
                }
                return ['id' => (int) $id, 'deleted' => true];
            });
        });
    }
}
