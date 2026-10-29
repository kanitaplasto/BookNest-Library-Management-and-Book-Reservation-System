<?php

require_once __DIR__ . '/../services/ReservationService.php';

class ReservationRoutes
{
    private $reservationService;

    public function __construct()
    {
        $this->reservationService = new ReservationService();
    }

    public function getAll()
    {
        return $this->reservationService->getAllReservations();
    }

    public function getById($id)
    {
        return $this->reservationService->getReservationById($id);
    }

    public function getByUserId($userId)
    {
        return $this->reservationService->getReservationsByUserId($userId);
    }

    public function create($data)
    {
        return $this->reservationService->createReservation($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->reservationService->updateReservationStatus($id, $status);
    }

    public function delete($id)
    {
        return $this->reservationService->deleteReservation($id);
    }
}
