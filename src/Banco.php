<?php

declare(strict_types=1);

final class Banco
{
    public static function conectar(): PDO
    {
        $db = new PDO(
            getenv('VENDAS_DSN') ?: 'mysql:host=localhost;dbname=vendas;charset=utf8mb4',
            getenv('VENDAS_USUARIO') ?: 'root',
            getenv('VENDAS_SENHA') ?: '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $db->exec('PRAGMA foreign_keys = ON');
        }

        return $db;
    }
}
