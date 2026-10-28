<?php

declare(strict_types=1);

require __DIR__ . '/../src/Banco.php';
require __DIR__ . '/../src/Validacao.php';
require __DIR__ . '/../src/Cadastros.php';
require __DIR__ . '/../src/Pedidos.php';

putenv('VENDAS_DSN=sqlite::memory:');
$db = Banco::conectar();
$db->exec((string) file_get_contents(__DIR__ . '/../database.sqlite.sql'));

$cadastros = new Cadastros($db);
$pedidos = new Pedidos($db);

$total = 0;
$falhas = 0;
$ok = function (string $nome, bool $condicao) use (&$total, &$falhas): void {
    $total++;
    if (!$condicao) {
        $falhas++;
        echo "FALHOU: $nome\n";
    }
};
$erro = function (string $nome, callable $f, string $trecho) use ($ok): void {
    try {
        $f();
        $ok($nome, false);
    } catch (DomainException $e) {
        $ok($nome, str_contains($e->getMessage(), $trecho));
    }
};

$cliente = $cadastros->salvar('clientes', null, ['nome' => 'Ana', 'sobrenome' => 'Souza', 'cpf' => '529.982.247-25']);
$vendedor = $cadastros->salvar('vendedores', null, ['nome' => 'Bruno', 'sobrenome' => 'Lima', 'email' => 'Bruno@Exemplo.com', 'cpf' => '11144477735']);
$produto = $cadastros->salvar('produtos', null, ['codigo' => 'P1', 'descricao' => 'Cabo', 'estoque' => '10', 'preco_custo' => '5', 'preco_venda' => '12,50']);

$ok('CPF sem máscara', $cadastros->buscar('clientes', $cliente)['cpf'] === '52998224725');
$ok('e-mail em minúsculo', $cadastros->buscar('vendedores', $vendedor)['email'] === 'bruno@exemplo.com');
$ok('preço com vírgula', (float) $cadastros->buscar('produtos', $produto)['preco_venda'] === 12.5);
$erro('CPF inválido', fn () => $cadastros->salvar('clientes', null, ['nome' => 'X', 'sobrenome' => 'Y', 'cpf' => '11111111111']), 'CPF inválido');
$erro('campo obrigatório', fn () => $cadastros->salvar('clientes', null, ['nome' => 'X', 'cpf' => '52998224725']), 'Sobrenome');
$erro('CPF duplicado', fn () => $cadastros->salvar('clientes', null, ['nome' => 'A', 'sobrenome' => 'B', 'cpf' => '52998224725']), 'Já existe');
$erro('cadastro inexistente', fn () => $cadastros->listar('usuarios; DROP TABLE clientes'), 'inexistente');
$ok('busca com aspas não quebra', $cadastros->listar('clientes', "' OR 1=1 --") === []);
$ok('busca por nome', count($cadastros->listar('clientes', 'sou')) === 1);

$pedido = $pedidos->criar(['cliente_id' => $cliente, 'vendedor_id' => $vendedor, 'emissao' => '2026-10-28', 'frete' => '10,00']);
$pedidos->adicionarItem($pedido, $produto, 4);
$ok('baixa no estoque', (int) $cadastros->buscar('produtos', $produto)['estoque'] === 6);
$ok('total com frete', (float) $pedidos->listar()[0]['total'] === 60.0);
$erro('estoque insuficiente', fn () => $pedidos->adicionarItem($pedido, $produto, 7), 'Estoque insuficiente');
$ok('estoque intacto após erro', (int) $cadastros->buscar('produtos', $produto)['estoque'] === 6);
$erro('excluir cliente com pedido', fn () => $cadastros->excluir('clientes', $cliente), 'ligado a um pedido');

$pedidos->removerItem((int) $pedidos->itens($pedido)[0]['id']);
$ok('devolve estoque ao remover item', (int) $cadastros->buscar('produtos', $produto)['estoque'] === 10);
$pedidos->adicionarItem($pedido, $produto, 2);
$pedidos->excluir($pedido);
$ok('devolve estoque ao excluir pedido', (int) $cadastros->buscar('produtos', $produto)['estoque'] === 10);
$ok('pedido excluído', $pedidos->listar() === []);

echo ($total - $falhas) . "/$total testes passaram\n";
exit($falhas > 0 ? 1 : 0);
