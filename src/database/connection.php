<?php

    if (basename($_SERVER["PHP_SELF"]) === "connection.php") {
        header("Location: ../../error.php?errorCode=403");
        exit();
    };

    class Database {
        private static ?PDO $pdo = null; // pdo pode ser nulo

        public static function getConnection(): PDO {
            if (self::$pdo === null) { // Se pdo for nulo, cria a conexão
                $config = [
                    "host" => "localhost",
                    "port" => "3306",
                    "dbname" => "sistema",
                    "user" => "root",
                    "password" => "",
                ];

                try {
                    self::$pdo = new PDO("mysql:host={$config["host"]}; port={$config["port"]}; dbname={$config["dbname"]}", $config["user"], $config["password"],
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Permite capturar e tratar erros corretamente
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Evita precisar usar fetch(PDO::FETCH_ASSOC) sempre
                            PDO::ATTR_EMULATE_PREPARES => false, // Evita SQL Injection e melhora a segurança
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Garante suporte a caracteres especiais
                        ]
                    );
                } catch (PDOException $e) {
                    error_log($e->getMessage(), 3, "/var/log/meu_sistema.log");
                    header("Location: ../../error.php?errorCode=500");
                    die();
                };
            };
            return self::$pdo;
        }
    };

?>