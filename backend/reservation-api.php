<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/routes/ReservationRoutes.php';

try {
    $reservationRoutes = new ReservationRoutes();

    $method = $_SERVER['REQUEST_METHOD'];

    // GET: Retrieve reservations
    if ($method === 'GET') {

        if (isset($_GET['id'])) {
            $result = $reservationRoutes->getById($_GET['id']);

        } elseif (isset($_GET['user_id'])) {
            $result = $reservationRoutes->getByUserId($_GET['user_id']);

        } else {
            $result = $reservationRoutes->getAll();
        }

        echo json_encode($result);
        exit;
    }

    // POST: Create or cancel a reservation
    if ($method === 'POST') {

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Invalid JSON data.'
            ]);

            exit;
        }

        // Cancel an existing reservation
        if (isset($data['action']) && $data['action'] === 'cancel') {

            if (!isset($data['id']) || !is_numeric($data['id'])) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'A valid reservation ID is required.'
                ]);

                exit;
            }

            $reservationRoutes->updateStatus(
                (int) $data['id'],
                'Cancelled'
            );

            echo json_encode([
                'success' => true,
                'message' => 'Reservation cancelled successfully.',
                'id' => (int) $data['id'],
                'status' => 'Cancelled'
            ]);

            exit;
        }

        // Create a new reservation
        $result = $reservationRoutes->create($data);

        http_response_code(201);

        echo json_encode([
            'message' => 'Reservation created successfully.',
            'id' => $result
        ]);

        exit;
    }

    // Unsupported HTTP method
    http_response_code(405);

    echo json_encode([
        'error' => 'Method not allowed.'
    ]);

} catch (Throwable $exception) {

    http_response_code(500);

    echo json_encode([
        'error' => $exception->getMessage()
    ]);
}
