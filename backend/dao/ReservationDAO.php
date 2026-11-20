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

    public function hasActiveReservation($userId, $bookId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*)
                FROM reservations
                WHERE user_id = ? AND book_id = ?
                  AND status IN ('Queued', 'Ready')";
        $parameters = [$userId, $bookId];

        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $parameters[] = $excludeId;
        }

        return (int) $this->executeQuery($sql, $parameters)->fetchColumn() > 0;
    }

    public function countActiveByUser($userId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*)
                FROM reservations
                WHERE user_id = ? AND status IN ('Queued', 'Ready')";
        $parameters = [$userId];

        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $parameters[] = $excludeId;
        }

        return (int) $this->executeQuery($sql, $parameters)->fetchColumn();
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

    public function update($id, $data)
    {
        $sql = "UPDATE reservations
                SET user_id = ?, book_id = ?, status = ?
                WHERE id = ?";

        $statement = $this->executeQuery($sql, [
            $data['user_id'],
            $data['book_id'],
            $data['status'],
            $id
        ]);

        return $statement->rowCount();
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE reservations SET status = ? WHERE id = ?";
        $statement = $this->executeQuery($sql, [$status, $id]);
        return $statement->rowCount();
    }

    public function delete($id)
    {
        $statement = $this->executeQuery(
            "DELETE FROM reservations WHERE id = ?",
            [$id]
        );
        return $statement->rowCount();
    }

}
