<?php

require_once __DIR__ . '/ApiException.php';

/** Shared operations for every BookNest business-logic service. */
abstract class BaseService
{
    protected $dao;

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById($id): ?array
    {
        $id = $this->validateId($id);
        $record = $this->dao->getById($id);

        return $record === false ? null : $record;
    }

    public function delete($id): bool
    {
        $id = $this->validateId($id);

        if ($this->getById($id) === null) {
            throw new ApiException('Record not found.', 404);
        }

        return (bool) $this->dao->delete($id);
    }

    protected function validateId($id): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1) {
            throw new InvalidArgumentException('ID must be a positive integer.');
        }

        return (int) $id;
    }

    protected function requireRecord($id, string $resourceName = 'Record'): array
    {
        $record = $this->getById($id);

        if ($record === null) {
            throw new ApiException($resourceName . ' not found.', 404);
        }

        return $record;
    }

    protected function optionalPositiveId($value, string $fieldName): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            throw new InvalidArgumentException($fieldName . ' must be a positive integer or null.');
        }

        return (int) $value;
    }
}
