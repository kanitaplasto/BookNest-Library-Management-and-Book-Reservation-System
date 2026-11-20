<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDAO.php';

class UserService extends BaseService
{
    private const ALLOWED_ROLES = ['member', 'librarian'];

    public function __construct()
    {
        parent::__construct(new UserDAO());
    }

    public function create(array $data): int
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $role = $data['role'] ?? 'member';

        if ($fullName === '' || mb_strlen($fullName) > 100) {
            throw new InvalidArgumentException('Full name is required and must not exceed 100 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            throw new InvalidArgumentException('A valid email address of at most 150 characters is required.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must contain at least 8 characters.');
        }
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException('Role must be member or librarian.');
        }
        if ($this->dao->getByEmail($email)) {
            throw new InvalidArgumentException('A user with this email address already exists.');
        }

        return (int) $this->dao->create([
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
    }

    public function update($id, array $data): array
    {
        $id = $this->validateId($id);
        $existing = $this->requireRecord($id, 'User');

        $fullName = trim((string) ($data['full_name'] ?? $existing['full_name']));
        $email = strtolower(trim((string) ($data['email'] ?? $existing['email'])));
        $role = $data['role'] ?? $existing['role'];

        if ($fullName === '' || mb_strlen($fullName) > 100) {
            throw new InvalidArgumentException('Full name is required and must not exceed 100 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            throw new InvalidArgumentException('A valid email address of at most 150 characters is required.');
        }
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException('Role must be member or librarian.');
        }

        $userWithEmail = $this->dao->getByEmail($email);
        if ($userWithEmail && (int) $userWithEmail['id'] !== $id) {
            throw new InvalidArgumentException('A user with this email address already exists.');
        }

        $updateData = [
            'full_name' => $fullName,
            'email' => $email,
            'role' => $role
        ];

        if (array_key_exists('password', $data) && $data['password'] !== '') {
            if (strlen((string) $data['password']) < 8) {
                throw new InvalidArgumentException('Password must contain at least 8 characters.');
            }
            $updateData['password'] = (string) $data['password'];
        }

        $this->dao->update($id, $updateData);
        return $this->requireRecord($id, 'User');
    }
}
