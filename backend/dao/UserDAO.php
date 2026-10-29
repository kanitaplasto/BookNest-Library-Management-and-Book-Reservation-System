<?php

require_once __DIR__ . '/BaseDAO.php';

class UserDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT id, full_name, email, role, created_at
                FROM users";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT id, full_name, email, role, created_at
                FROM users
                WHERE id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function getByEmail($email)
    {
        $sql = "SELECT id, full_name, email, role, created_at
                FROM users
                WHERE email = ?";

        return $this->fetchOne($sql, [$email]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (full_name, email, password, role)
                VALUES (?, ?, ?, ?)";

        $this->executeQuery($sql, [
            $data['full_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'member'
        ]);

        return $this->connection->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE users
                SET full_name = ?, email = ?, role = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['full_name'],
            $data['email'],
            $data['role'],
            $id
        ]);

        return $statement->rowCount();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = ?";

        $statement = $this->executeQuery($sql, [$id]);

        return $statement->rowCount();
    }
}
