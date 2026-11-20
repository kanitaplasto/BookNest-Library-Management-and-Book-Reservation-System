<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReservationDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../dao/BookDAO.php';

class ReservationService extends BaseService
{
    private const ALLOWED_STATUSES = ['Queued', 'Ready', 'Completed', 'Cancelled'];
    private const MAX_ACTIVE_RESERVATIONS = 3;

    private UserDAO $userDAO;
    private BookDAO $bookDAO;

    public function __construct()
    {
        parent::__construct(new ReservationDAO());
        $this->userDAO = new UserDAO();
        $this->bookDAO = new BookDAO();
    }

    public function create(array $data): int
    {
        $userId = $this->validateId($data['user_id'] ?? 0);
        $bookId = $this->validateId($data['book_id'] ?? 0);

        if (!$this->userDAO->getById($userId)) {
            throw new InvalidArgumentException('The selected user does not exist.');
        }
        if (!$this->bookDAO->getById($bookId)) {
            throw new InvalidArgumentException('The selected book does not exist.');
        }
        if ($this->dao->hasActiveReservation($userId, $bookId)) {
            throw new InvalidArgumentException('This user already has an active reservation for this book.');
        }
        if ($this->dao->countActiveByUser($userId) >= self::MAX_ACTIVE_RESERVATIONS) {
            throw new InvalidArgumentException('A user can have up to 3 active reservations.');
        }

        return (int) $this->dao->create([
            'user_id' => $userId,
            'book_id' => $bookId,
            'status' => 'Queued'
        ]);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'Reservation');
        $userId = $this->validateId($data['user_id'] ?? $existing['user_id']);
        $bookId = $this->validateId($data['book_id'] ?? $existing['book_id']);
        $status = $data['status'] ?? $existing['status'];

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid reservation status.');
        }
        if (!$this->userDAO->getById($userId)) {
            throw new InvalidArgumentException('The selected user does not exist.');
        }
        if (!$this->bookDAO->getById($bookId)) {
            throw new InvalidArgumentException('The selected book does not exist.');
        }
        if (in_array($status, ['Queued', 'Ready'], true)) {
            if ($this->dao->hasActiveReservation($userId, $bookId, $id)) {
                throw new InvalidArgumentException('This user already has an active reservation for this book.');
            }
            if ($this->dao->countActiveByUser($userId, $id) >= self::MAX_ACTIVE_RESERVATIONS) {
                throw new InvalidArgumentException('A user can have up to 3 active reservations.');
            }
        }

        $this->dao->update($id, [
            'user_id' => $userId,
            'book_id' => $bookId,
            'status' => $status
        ]);

        return $this->requireRecord($id, 'Reservation');
    }

    public function getByUserId($userId): array
    {
        $userId = $this->validateId($userId);
        if (!$this->userDAO->getById($userId)) {
            throw new ApiException('User not found.', 404);
        }
        return $this->dao->getByUserId($userId);
    }

    public function updateReservationStatus($id, string $status): array
    {
        return $this->update($id, ['status' => $status]);
    }

    // Compatibility methods retained for the original ReservationRoutes class.
    public function getAllReservations(): array
    {
        return $this->getAll();
    }

    public function getReservationById($id): ?array
    {
        return $this->getById($id);
    }

    public function getReservationsByUserId($userId): array
    {
        return $this->getByUserId($userId);
    }

    public function createReservation(array $data): int
    {
        return $this->create($data);
    }

    public function deleteReservation($id): bool
    {
        return $this->delete($id);
    }
}
