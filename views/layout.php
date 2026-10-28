<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Sistema de Vendas</title>
    <link href="vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="?p=pedidos">Sistema de Vendas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav">
                <?php foreach (['pedidos' => 'Pedidos', 'clientes' => 'Clientes', 'vendedores' => 'Vendedores', 'produtos' => 'Produtos'] as $p => $rotulo): ?>
                    <li class="nav-item"><a class="nav-link<?= in_array($pagina, [$p, rtrim($p, 's')], true) ? ' active' : '' ?>" href="?p=<?= $p ?>"><?= $rotulo ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if ($aviso): ?>
        <div class="alert alert-<?= e($aviso['tipo']) ?>"><?= e($aviso['mensagem']) ?></div>
    <?php endif; ?>
    <?php require $conteudo; ?>
</main>

<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
