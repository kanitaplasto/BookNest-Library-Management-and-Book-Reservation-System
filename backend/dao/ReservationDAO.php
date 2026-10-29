<?php

require_once __DIR__ . '/BaseDAO.php';

class ReservationDAO extends BaseDAO
{
    public function getAll()
    {
        $sql = "SELECT reservations.id,
                       reservations.user_id,
                       reservations.book_id,
                       reservations.reservation_date,
                       reservations.status,
                       books.title AS book_title,
                       users.full_name AS user_name
                FROM reservations
                JOIN books ON reservations.book_id = books.id
                JOIN users ON reservations.user_id = users.id
                ORDER BY reservations.reservation_date DESC";

        return $this->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT reservations.id,
                       reservations.user_id,
                       reservations.book_id,
                       reservations.reservation_date,
                       reservations.status,
                       books.title AS book_title,
                       users.full_name AS user_name
                FROM reservations
                JOIN books ON reservations.book_id = books.id
                JOIN users ON reservations.user_id = users.id
                WHERE reservations.id = ?";

        return $this->fetchOne($sql, [$id]);
    }

    public function getByUserId($userId)
    {
        $sql = "SELECT reservations.id,
                       reservations.user_id,
                       reservations.book_id,
                       reservations.reservation_date,
                       reservations.status,
                       books.title AS book_title
                FROM reservations
                JOIN books ON reservations.book_id = books.id
                WHERE reservations.user_id = ?
                ORDER BY reservations.reservation_date DESC";

        return $this->fetchAll($sql, [$userId]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO reservations
                    (user_id, book_id, reservation_date, status)
                VALUES (?, ?, ?, ?)";

        $this->executeQuery($sql, [
            $data['user_id'],
            $data['book_id'],
            $data['reservation_date'] ?? date('Y-m-d H:i:s'),
            $data['status'] ?? 'Queued'
        ]);

        return $this->connection->lastInsertId();
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE reservations
                SET status = ?
                WHERE id = ?";

        $this->executeQuery($sql, [$status, $id]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM reservations
                WHERE id = ?";

        $this->executeQuery($sql, [$id]);
    }
}
