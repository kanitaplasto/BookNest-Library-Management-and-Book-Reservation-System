<?php

class BaseDAO
{
    protected $connection;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';

        $host = $config['host'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'];

        $dsn = "mysql:host=$host;dbname=$database;charset=$charset";

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());

            throw new RuntimeException(
                'Unable to connect to the database.'
            );
        }
    }

    protected function executeQuery($sql, $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement;
    }

    protected function fetchAll($sql, $parameters = [])
    {
        return $this->executeQuery($sql, $parameters)->fetchAll();
    }

    protected function fetchOne($sql, $parameters = [])
    {
        return $this->executeQuery($sql, $parameters)->fetch();
    }
}