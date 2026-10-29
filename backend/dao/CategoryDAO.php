<?php

require_once __DIR__ . '/BaseDAO.php';

class CategoryDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT id, name, description
                FROM categories";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT id, name, description
                FROM categories
                WHERE id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO categories (name, description)
                VALUES (?, ?)";

        $this->executeQuery($sql, [
            $data['name'],
            $data['description'] ?? null
        ]);

        return $this->connection->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE categories
                SET name = ?, description = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['name'],
            $data['description'] ?? null,
            $id
        ]);

        return $statement->rowCount();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM categories WHERE id = ?";

        $statement = $this->executeQuery($sql, [$id]);

        return $statement->rowCount();
    }
}
