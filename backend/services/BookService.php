<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookDAO.php';
require_once __DIR__ . '/../dao/AuthorDAO.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';

class BookService extends BaseService
{
    private AuthorDAO $authorDAO;
    private CategoryDAO $categoryDAO;

    public function __construct()
    {
        parent::__construct(new BookDAO());
        $this->authorDAO = new AuthorDAO();
        $this->categoryDAO = new CategoryDAO();
    }

    public function create(array $data): int
    {
        $clean = $this->validateBookData($data);

        if ($clean['isbn'] !== null && $this->dao->getByIsbn($clean['isbn'])) {
            throw new InvalidArgumentException('A book with this ISBN already exists.');
        }

        return (int) $this->dao->create($clean);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'Book');
        $clean = $this->validateBookData($data, $existing);

        if ($clean['isbn'] !== null) {
            $bookWithIsbn = $this->dao->getByIsbn($clean['isbn']);
            if ($bookWithIsbn && (int) $bookWithIsbn['id'] !== $id) {
                throw new InvalidArgumentException('A book with this ISBN already exists.');
            }
        }

        $this->dao->update($id, $clean);
        return $this->requireRecord($id, 'Book');
    }

    private function fieldValue(array $data, string $field, ?array $existing, $default)
    {
        if (array_key_exists($field, $data)) {
            return $data[$field];
        }
        return $existing[$field] ?? $default;
    }

    private function validateBookData(array $data, ?array $existing = null): array
    {
        $title = trim((string) ($data['title'] ?? ($existing['title'] ?? '')));
        $isbnValue = trim((string) $this->fieldValue($data, 'isbn', $existing, ''));
        $yearValue = $this->fieldValue($data, 'publication_year', $existing, null);
        $description = trim((string) $this->fieldValue($data, 'description', $existing, ''));
        $totalValue = $this->fieldValue($data, 'total_copies', $existing, 1);
        $availableValue = $this->fieldValue($data, 'available_copies', $existing, 1);
        $authorId = $this->optionalPositiveId($this->fieldValue($data, 'author_id', $existing, null), 'author_id');
        $categoryId = $this->optionalPositiveId($this->fieldValue($data, 'category_id', $existing, null), 'category_id');

        if ($title === '' || mb_strlen($title) > 200) {
            throw new InvalidArgumentException('Book title is required and must not exceed 200 characters.');
        }
        if ($isbnValue !== '' && mb_strlen($isbnValue) > 20) {
            throw new InvalidArgumentException('ISBN must not exceed 20 characters.');
        }

        $year = null;
        if ($yearValue !== null && $yearValue !== '') {
            if (filter_var($yearValue, FILTER_VALIDATE_INT) === false) {
                throw new InvalidArgumentException('Publication year must be an integer.');
            }
            $year = (int) $yearValue;
            if ($year < 0 || $year > ((int) date('Y') + 5)) {
                throw new InvalidArgumentException('Publication year is outside the allowed range.');
            }
        }

        if (filter_var($totalValue, FILTER_VALIDATE_INT) === false || (int) $totalValue < 0) {
            throw new InvalidArgumentException('Total copies must be a non-negative integer.');
        }
        if (filter_var($availableValue, FILTER_VALIDATE_INT) === false || (int) $availableValue < 0) {
            throw new InvalidArgumentException('Available copies must be a non-negative integer.');
        }

        $total = (int) $totalValue;
        $available = (int) $availableValue;
        if ($available > $total) {
            throw new InvalidArgumentException('Available copies cannot exceed total copies.');
        }
        if ($authorId !== null && !$this->authorDAO->getById($authorId)) {
            throw new InvalidArgumentException('The selected author does not exist.');
        }
        if ($categoryId !== null && !$this->categoryDAO->getById($categoryId)) {
            throw new InvalidArgumentException('The selected category does not exist.');
        }

        return [
            'title' => $title,
            'isbn' => $isbnValue === '' ? null : $isbnValue,
            'publication_year' => $year,
            'description' => $description === '' ? null : $description,
            'total_copies' => $total,
            'available_copies' => $available,
            'author_id' => $authorId,
            'category_id' => $categoryId
        ];
    }
}
