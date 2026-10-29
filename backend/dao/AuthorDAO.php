<?php

require_once __DIR__ . '/BaseDAO.php';

class AuthorDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT id, first_name, last_name, biography
                FROM authors";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT id, first_name, last_name, biography
                FROM authors
                WHERE id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO authors (first_name, last_name, biography)
                VALUES (?, ?, ?)";

        $this->executeQuery($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['biography'] ?? null
        ]);

        return $this->connection->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE authors
                SET first_name = ?, last_name = ?, biography = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['biography'] ?? null,
            $id
        ]);

        return $statement->rowCount();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM authors WHERE id = ?";

        $statement = $this->executeQuery($sql, [$id]);

        return $statement->rowCount();
    }
}
