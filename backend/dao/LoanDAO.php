<?php

require_once __DIR__ . '/BaseDAO.php';

class LoanDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT id, book_id, user_id, borrowed_date,
                       due_date, returned_date, status
                FROM loans";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT id, book_id, user_id, borrowed_date,
                       due_date, returned_date, status
                FROM loans
                WHERE id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO loans
                (book_id, user_id, borrowed_date, due_date,
                 returned_date, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $this->executeQuery($sql, [
            $data['book_id'],
            $data['user_id'],
            $data['borrowed_date'],
            $data['due_date'],
            $data['returned_date'] ?? null,
            $data['status'] ?? 'Borrowed'
        ]);

        return $this->connection->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE loans
                SET book_id = ?,
                    user_id = ?,
                    borrowed_date = ?,
                    due_date = ?,
                    returned_date = ?,
                    status = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['book_id'],
            $data['user_id'],
            $data['borrowed_date'],
            $data['due_date'],
            $data['returned_date'] ?? null,
            $data['status'],
            $id
        ]);

        return $statement->rowCount();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM loans WHERE id = ?";

        $statement = $this->executeQuery($sql, [$id]);

        return $statement->rowCount();
    }
}
