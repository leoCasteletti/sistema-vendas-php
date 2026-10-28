<?php
$clientes = $cadastros->listar('clientes');
$vendedores = $cadastros->listar('vendedores');
$lista = $pedidos->listar();
?>
<h1 class="h3 mb-4">Pedidos</h1>

<?php if ($clientes === [] || $vendedores === []): ?>
    <div class="alert alert-info">Cadastre pelo menos um <a href="?p=clientes">cliente</a> e um <a href="?p=vendedores">vendedor</a> para criar pedidos.</div>
<?php else: ?>
    <form method="post" action="?p=pedidos" class="card card-body mb-4">
        <?= csrf() ?>
        <h2 class="h5">Novo pedido</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="cliente_id">Cliente</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($clientes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['nome'] . ' ' . $c['sobrenome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="vendedor_id">Vendedor</label>
                <select class="form-select" id="vendedor_id" name="vendedor_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($vendedores as $v): ?><option value="<?= (int) $v['id'] ?>"><?= e($v['nome'] . ' ' . $v['sobrenome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="emissao">Emissão</label>
                <input class="form-control" id="emissao" name="emissao" type="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="nota_fiscal">Nota fiscal</label>
                <input class="form-control" id="nota_fiscal" name="nota_fiscal">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="frete">Frete</label>
                <input class="form-control" id="frete" name="frete" inputmode="decimal" placeholder="0,00">
            </div>
        </div>
        <div class="mt-3"><button class="btn btn-dark" name="acao" value="criar-pedido">Criar pedido</button></div>
    </form>
<?php endif; ?>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Nº</th><th>Emissão</th><th>Cliente</th><th>Vendedor</th><th>NF</th><th class="text-end">Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $p): ?>
            <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><?= dataBr($p['emissao']) ?></td>
                <td><?= e($p['cliente_nome'] . ' ' . $p['cliente_sobrenome']) ?></td>
                <td><?= e($p['vendedor_nome'] . ' ' . $p['vendedor_sobrenome']) ?></td>
                <td><?= e($p['nota_fiscal']) ?></td>
                <td class="text-end"><?= moeda($p['total']) ?></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?p=pedido&amp;id=<?= (int) $p['id'] ?>">Abrir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($lista === []): ?>
            <tr><td colspan="7" class="text-secondary">Nenhum pedido.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
