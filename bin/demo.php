<?php

declare(strict_types=1);

require __DIR__ . '/../src/Banco.php';
require __DIR__ . '/../src/Validacao.php';
require __DIR__ . '/../src/Cadastros.php';
require __DIR__ . '/../src/Pedidos.php';

$db = Banco::conectar();

if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    $existe = $db->query("SELECT name FROM sqlite_master WHERE name = 'clientes'")->fetch();
    if (!$existe) {
        $db->exec((string) file_get_contents(__DIR__ . '/../database.sqlite.sql'));
    }
}

$cadastros = new Cadastros($db);
$pedidos = new Pedidos($db);

if ($cadastros->listar('clientes') !== []) {
    fwrite(STDERR, "O banco já tem dados.\n");
    exit(1);
}

$clientes = [
    ['Mariana', 'Alves', '52998224725', '1990-04-12', '(11) 3333-0101', 'Rua das Flores, 120'],
    ['Paulo', 'Ribeiro', '11144477735', '1985-09-30', '(11) 3333-0202', 'Av. Central, 45'],
    ['Juliana', 'Costa', '39053344705', '1998-01-07', '(11) 3333-0303', 'Rua Sete, 800'],
];
foreach ($clientes as [$nome, $sobrenome, $cpf, $nascimento, $telefone, $endereco]) {
    $cadastros->salvar('clientes', null, compact('nome', 'sobrenome', 'cpf', 'nascimento', 'telefone', 'endereco'));
}

$cadastros->salvar('vendedores', null, ['nome' => 'Carlos', 'sobrenome' => 'Mendes', 'email' => 'carlos@exemplo.com', 'cpf' => '15350946056']);
$cadastros->salvar('vendedores', null, ['nome' => 'Fernanda', 'sobrenome' => 'Lopes', 'email' => 'fernanda@exemplo.com', 'cpf' => '86288366757']);

$produtos = [
    ['TEC-01', 'Teclado mecânico', 'Distribuidora Alfa', 15, '120,00', '219,90'],
    ['MOU-02', 'Mouse sem fio', 'Distribuidora Alfa', 30, '35,00', '79,90'],
    ['MON-24', 'Monitor 24 polegadas', 'Tech Import', 8, '650,00', '999,00'],
    ['HUB-USB', 'Hub USB-C 7 em 1', 'Tech Import', 20, '90,00', '169,90'],
];
foreach ($produtos as [$codigo, $descricao, $fornecedor, $estoque, $preco_custo, $preco_venda]) {
    $cadastros->salvar('produtos', null, compact('codigo', 'descricao', 'fornecedor', 'estoque', 'preco_custo', 'preco_venda'));
}

$p1 = $pedidos->criar(['cliente_id' => 1, 'vendedor_id' => 1, 'emissao' => '2026-10-20', 'nota_fiscal' => '1201', 'frete' => '25,00']);
$pedidos->adicionarItem($p1, 1, 1);
$pedidos->adicionarItem($p1, 2, 2);
$p2 = $pedidos->criar(['cliente_id' => 2, 'vendedor_id' => 2, 'emissao' => '2026-10-24', 'nota_fiscal' => '1202', 'frete' => '0']);
$pedidos->adicionarItem($p2, 3, 2);
$pedidos->adicionarItem($p2, 4, 1);

echo "Dados de demonstração criados.\n";
