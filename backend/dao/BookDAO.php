<?php

require_once __DIR__ . '/BaseDAO.php';

class BookDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT id, title, isbn, publication_year, description,
                       total_copies, available_copies, author_id,
                       category_id, created_at
                FROM books";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT id, title, isbn, publication_year, description,
                       total_copies, available_copies, author_id,
                       category_id, created_at
                FROM books
                WHERE id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO books
                (title, isbn, publication_year, description,
                 total_copies, available_copies, author_id, category_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $this->executeQuery($sql, [
            $data['title'],
            $data['isbn'],
            $data['publication_year'],
            $data['description'] ?? null,
            $data['total_copies'],
            $data['available_copies'],
            $data['author_id'],
            $data['category_id']
        ]);

        return $this->connection->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE books
                SET title = ?,
                    isbn = ?,
                    publication_year = ?,
                    description = ?,
                    total_copies = ?,
                    available_copies = ?,
                    author_id = ?,
                    category_id = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['title'],
            $data['isbn'],
            $data['publication_year'],
            $data['description'] ?? null,
            $data['total_copies'],
            $data['available_copies'],
            $data['author_id'],
            $data['category_id'],
            $id
        ]);

        return $statement->rowCount();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM books WHERE id = ?";

        $statement = $this->executeQuery($sql, [$id]);

        return $statement->rowCount();
    }
}
