<?php

require_once __DIR__ . '/../dao/ReservationDAO.php';

class ReservationService
{
    private $reservationDAO;

    public function __construct()
    {
        $this->reservationDAO = new ReservationDAO();
    }

    public function getAllReservations()
    {
        return $this->reservationDAO->getAll();
    }

    public function getReservationById($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException(
                'Invalid reservation ID.'
            );
        }

        return $this->reservationDAO->getById((int) $id);
    }

    public function getReservationsByUserId($userId)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        return $this->reservationDAO->getByUserId((int) $userId);
    }

    public function createReservation($data)
    {
        if (
            empty($data['user_id']) ||
            empty($data['book_id'])
        ) {
            throw new InvalidArgumentException(
                'User ID and book ID are required.'
            );
        }

        return $this->reservationDAO->create($data);
    }

    public function updateReservationStatus($id, $status)
    {
        $allowedStatuses = [
            'Queued',
            'Ready',
            'Cancelled'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid reservation status.'
            );
        }

        return $this->reservationDAO->updateStatus(
            $id,
            $status
        );
    }

    public function deleteReservation($id)
    {
        return $this->reservationDAO->delete($id);
    }
}