<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AuthorDAO.php';

class AuthorService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new AuthorDAO());
    }

    public function create(array $data): int
    {
        $clean = $this->validateAuthorData($data);
        return (int) $this->dao->create($clean);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'Author');
        $clean = $this->validateAuthorData($data, $existing);
        $this->dao->update($id, $clean);
        return $this->requireRecord($id, 'Author');
    }

    private function validateAuthorData(array $data, ?array $existing = null): array
    {
        $firstName = trim((string) ($data['first_name'] ?? ($existing['first_name'] ?? '')));
        $lastName = trim((string) ($data['last_name'] ?? ($existing['last_name'] ?? '')));
        $biography = trim((string) ($data['biography'] ?? ($existing['biography'] ?? '')));

        if ($firstName === '' || mb_strlen($firstName) > 100) {
            throw new InvalidArgumentException('Author first name is required and must not exceed 100 characters.');
        }
        if ($lastName === '' || mb_strlen($lastName) > 100) {
            throw new InvalidArgumentException('Author last name is required and must not exceed 100 characters.');
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'biography' => $biography === '' ? null : $biography
        ];
    }
}
