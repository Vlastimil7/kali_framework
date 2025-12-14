<?php

namespace Core;

use Helpers\Logger;


class Database
{
    // Statická proměnná pro singleton instanci
    private static $instance = null;
    private static $config = null;

    private $host;
    private $dbname;
    private $username;
    private $password;
    private $pdo;
    private $inTransaction = false;

    // Metoda pro nastavení konfigurace
    public static function setConfig($config)
    {
        if (!isset($config['db'])) {
            Logger::error("Invalid database configuration format");
            throw new \Exception("Invalid database configuration format");
        }
        self::$config = $config;
    }


    // Privátní konstruktor - zabrání vytvoření instance mimo třídu
    private function __construct()
    {

        
        if (self::$config === null) {
            Logger::error("Database configuration not set. Call Database::setConfig() first.");
            throw new \Exception("Database configuration not set. Call Database::setConfig() first.");
        }

        $this->host = self::$config['db']['host'];
        $this->dbname = self::$config['db']['dbname'];
        $this->username = self::$config['db']['username'];
        $this->password = self::$config['db']['password'];

        // Spojení se vytvoří při prvním volání getConnection()
    }

    // Metoda pro získání instance Database (singleton pattern)
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Metoda pro získání připojení - lazy loading
    private function getConnection()
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->pdo = new \PDO($dsn, $this->username, $this->password, $options);


        } catch (\PDOException $e) {
            // Log chyby do souboru s časovým razítkem
            Logger::error("Database connection error: " . $e->getMessage());


            die("Database connection error: " . $e->getMessage());
        }
    }

    // Metoda pro explicitní ukončení spojení
    public function closeConnection()
    {
        if ($this->pdo !== null) {
            

            $this->pdo = null;
        }
    }

    // Původní metody s voláním getConnection()
    public function query($sql)
    {
        return $this->getConnection()->query($sql);
    }

    public function prepare($sql)
    {
        return $this->getConnection()->prepare($sql);
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->inTransaction = true;
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        $this->inTransaction = false;
        return $this->getConnection()->commit();
    }

    public function rollback()
    {
        $this->inTransaction = false;
        return $this->getConnection()->rollBack();
    }

    public function inTransaction()
    {
        return $this->inTransaction;
    }
}
