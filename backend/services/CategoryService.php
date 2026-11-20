<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';

class CategoryService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new CategoryDAO());
    }

    public function create(array $data): int
    {
        $clean = $this->validateCategoryData($data);
        if ($this->dao->getByName($clean['name'])) {
            throw new InvalidArgumentException('A category with this name already exists.');
        }
        return (int) $this->dao->create($clean);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'Category');
        $clean = $this->validateCategoryData($data, $existing);
        $category = $this->dao->getByName($clean['name']);

        if ($category && (int) $category['id'] !== $id) {
            throw new InvalidArgumentException('A category with this name already exists.');
        }

        $this->dao->update($id, $clean);
        return $this->requireRecord($id, 'Category');
    }

    private function validateCategoryData(array $data, ?array $existing = null): array
    {
        $name = trim((string) ($data['name'] ?? ($existing['name'] ?? '')));
        $description = trim((string) ($data['description'] ?? ($existing['description'] ?? '')));

        if ($name === '' || mb_strlen($name) > 100) {
            throw new InvalidArgumentException('Category name is required and must not exceed 100 characters.');
        }

        return [
            'name' => $name,
            'description' => $description === '' ? null : $description
        ];
    }
}
