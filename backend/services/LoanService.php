<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/LoanDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../dao/BookDAO.php';

class LoanService extends BaseService
{
    private UserDAO $userDAO;
    private BookDAO $bookDAO;

    public function __construct()
    {
        parent::__construct(new LoanDAO());
        $this->userDAO = new UserDAO();
        $this->bookDAO = new BookDAO();
    }

    public function create(array $data): int
    {
        $clean = $this->validateLoanData($data);
        return (int) $this->dao->create($clean);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'Loan');
        $clean = $this->validateLoanData($data, $existing);
        $this->dao->update($id, $clean);
        return $this->requireRecord($id, 'Loan');
    }

    private function validateLoanData(array $data, ?array $existing = null): array
    {
        $bookId = $this->validateId($data['book_id'] ?? ($existing['book_id'] ?? 0));
        $userId = $this->validateId($data['user_id'] ?? ($existing['user_id'] ?? 0));
        $borrowedDate = $this->validateDate(
            $data['borrowed_date'] ?? ($existing['borrowed_date'] ?? ''),
            'borrowed_date'
        );
        $dueDate = $this->validateDate(
            $data['due_date'] ?? ($existing['due_date'] ?? ''),
            'due_date'
        );
        $returnedValue = $data['returned_date'] ?? ($existing['returned_date'] ?? null);
        $returnedDate = ($returnedValue === null || $returnedValue === '')
            ? null
            : $this->validateDate($returnedValue, 'returned_date');
        $status = $data['status'] ?? ($existing['status'] ?? 'Borrowed');

        if (!$this->userDAO->getById($userId)) {
            throw new InvalidArgumentException('The selected user does not exist.');
        }
        if (!$this->bookDAO->getById($bookId)) {
            throw new InvalidArgumentException('The selected book does not exist.');
        }
        if ($dueDate < $borrowedDate) {
            throw new InvalidArgumentException('Due date cannot be earlier than the borrowed date.');
        }
        if ($returnedDate !== null && $returnedDate < $borrowedDate) {
            throw new InvalidArgumentException('Returned date cannot be earlier than the borrowed date.');
        }
        if (!in_array($status, ['Borrowed', 'Returned', 'Overdue'], true)) {
            throw new InvalidArgumentException('Loan status must be Borrowed, Returned, or Overdue.');
        }

        if ($returnedDate !== null) {
            $status = 'Returned';
        } elseif ($status === 'Returned') {
            throw new InvalidArgumentException('A returned loan must include returned_date.');
        } elseif ($dueDate < date('Y-m-d')) {
            $status = 'Overdue';
        }

        return [
            'book_id' => $bookId,
            'user_id' => $userId,
            'borrowed_date' => $borrowedDate,
            'due_date' => $dueDate,
            'returned_date' => $returnedDate,
            'status' => $status
        ];
    }

    private function validateDate($value, string $fieldName): string
    {
        if (!is_string($value) || $value === '') {
            throw new InvalidArgumentException($fieldName . ' must use YYYY-MM-DD format.');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException($fieldName . ' must use a valid YYYY-MM-DD date.');
        }

        return $value;
    }
}
