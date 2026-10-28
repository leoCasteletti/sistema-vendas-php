<?php

declare(strict_types=1);

require __DIR__ . '/../src/Banco.php';
require __DIR__ . '/../src/Validacao.php';
require __DIR__ . '/../src/Cadastros.php';
require __DIR__ . '/../src/Pedidos.php';
require __DIR__ . '/../src/web.php';

session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

try {
    $db = Banco::conectar();
} catch (PDOException) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Confira o README.');
}

$cadastros = new Cadastros($db);
$pedidos = new Pedidos($db);

$pagina = $_GET['p'] ?? 'pedidos';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $acao = $_POST['acao'] ?? '';

    try {
        switch ($acao) {
            case 'salvar':
                $cadastros->salvar($pagina, $id, $_POST);
                avisar('Registro salvo.');
                redirecionar($pagina);

            case 'excluir':
                $cadastros->excluir($pagina, (int) $_POST['id']);
                avisar('Registro excluído.');
                redirecionar($pagina);

            case 'criar-pedido':
                $novo = $pedidos->criar($_POST);
                avisar('Pedido criado. Adicione os itens.');
                redirecionar('pedido', ['id' => $novo]);

            case 'adicionar-item':
                $pedidos->adicionarItem($id, (int) $_POST['produto_id'], (int) $_POST['quantidade']);
                redirecionar('pedido', ['id' => $id]);

            case 'remover-item':
                $pedidos->removerItem((int) $_POST['item_id']);
                redirecionar('pedido', ['id' => $id]);

            case 'excluir-pedido':
                $pedidos->excluir($id);
                avisar('Pedido excluído e estoque devolvido.');
                redirecionar('pedidos');
        }
    } catch (DomainException $e) {
        avisar($e->getMessage(), 'danger');
        $_SESSION['formulario'] = $_POST;
        redirecionar($_GET['p'] ?? 'pedidos', array_filter(['id' => $id, 'editar' => $_GET['editar'] ?? null]));
    }
}

$titulo = match (true) {
    isset(Cadastros::ENTIDADES[$pagina]) => Cadastros::ENTIDADES[$pagina]['titulo'],
    $pagina === 'pedido' => "Pedido $id",
    default => 'Pedidos',
};
$view = match (true) {
    isset(Cadastros::ENTIDADES[$pagina]) => 'cadastro',
    $pagina === 'pedido' && $id && $pedidos->buscar($id) => 'pedido',
    default => 'pedidos',
};
if ($view === 'pedidos') {
    $pagina = 'pedidos';
}

renderizar($view, $titulo, compact('cadastros', 'pedidos', 'pagina', 'id'));
