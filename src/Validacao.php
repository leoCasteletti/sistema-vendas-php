<?php

declare(strict_types=1);

final class Validacao
{
    public static function cpf(string $valor): string
    {
        $cpf = preg_replace('/\D/', '', $valor) ?? '';

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new DomainException('CPF inválido.');
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * ($t + 1 - $i);
            }
            if ((int) $cpf[$t] !== ((10 * $soma) % 11) % 10) {
                throw new DomainException('CPF inválido.');
            }
        }

        return $cpf;
    }

    public static function email(string $valor): string
    {
        $email = filter_var($valor, FILTER_VALIDATE_EMAIL);
        if ($email === false) {
            throw new DomainException('E-mail inválido.');
        }

        return mb_strtolower($email);
    }

    public static function data(string $valor, string $rotulo): string
    {
        $data = DateTime::createFromFormat('!Y-m-d', $valor);
        if (!$data || $data->format('Y-m-d') !== $valor) {
            throw new DomainException("Data inválida em $rotulo.");
        }

        return $valor;
    }

    public static function inteiro(string $valor, string $rotulo): int
    {
        if (!preg_match('/^\d+$/', $valor)) {
            throw new DomainException("$rotulo deve ser um número inteiro.");
        }

        return (int) $valor;
    }

    public static function dinheiro(string $valor, string $rotulo): string
    {
        $normalizado = str_contains($valor, ',') ? str_replace(['.', ','], ['', '.'], $valor) : $valor;
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $normalizado)) {
            throw new DomainException("Valor inválido em $rotulo.");
        }

        return number_format((float) $normalizado, 2, '.', '');
    }
}
