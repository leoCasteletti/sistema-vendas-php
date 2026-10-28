<?php
$pedido = $pedidos->buscar($id);
$itens = $pedidos->itens($id);
$produtos = $cadastros->listar('produtos');
$subtotal = array_sum(array_column($itens, 'subtotal'));
?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a class="small" href="?p=pedidos">Pedidos</a>
        <h1 class="h3 mb-1">Pedido <?= (int) $pedido['id'] ?></h1>
        <p class="text-secondary mb-0">
            <?= dataBr($pedido['emissao']) ?> ·
            <?= e($pedido['cliente_nome'] . ' ' . $pedido['cliente_sobrenome']) ?> ·
            vendedor <?= e($pedido['vendedor_nome'] . ' ' . $pedido['vendedor_sobrenome']) ?>
            <?= $pedido['nota_fiscal'] ? '· NF ' . e($pedido['nota_fiscal']) : '' ?>
        </p>
    </div>
    <form method="post" action="?p=pedido&amp;id=<?= (int) $id ?>">
        <?= csrf() ?>
        <button class="btn btn-sm btn-outline-danger" name="acao" value="excluir-pedido">Excluir pedido</button>
    </form>
</div>

<form method="post" action="?p=pedido&amp;id=<?= (int) $id ?>" class="card card-body mb-4">
    <?= csrf() ?>
    <div class="row g-3 align-items-end">
        <div class="col-md-7">
            <label class="form-label" for="produto_id">Produto</label>
            <select class="form-select" id="produto_id" name="produto_id" required>
                <option value="">Selecione</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= e($p['codigo'] . ' · ' . $p['descricao']) ?> (<?= moeda($p['preco_venda']) ?>, estoque <?= (int) $p['estoque'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="quantidade">Quantidade</label>
            <input class="form-control" id="quantidade" name="quantidade" type="number" min="1" value="1" required>
        </div>
        <div class="col-md-3 d-grid"><button class="btn btn-dark" name="acao" value="adicionar-item">Adicionar</button></div>
    </div>
</form>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Código</th><th>Produto</th><th class="text-end">Qtd.</th><th class="text-end">Unitário</th><th class="text-end">Subtotal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($itens as $i): ?>
            <tr>
                <td><code><?= e($i['codigo']) ?></code></td>
                <td><?= e($i['descricao']) ?></td>
                <td class="text-end"><?= (int) $i['quantidade'] ?></td>
                <td class="text-end"><?= moeda($i['valor_unitario']) ?></td>
                <td class="text-end"><?= moeda($i['subtotal']) ?></td>
                <td class="text-end">
                    <form method="post" action="?p=pedido&amp;id=<?= (int) $id ?>">
                        <?= csrf() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $i['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" name="acao" value="remover-item">Remover</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($itens === []): ?>
            <tr><td colspan="6" class="text-secondary">Nenhum item.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="text-end">Itens</td><td class="text-end"><?= moeda($subtotal) ?></td><td></td></tr>
            <tr><td colspan="4" class="text-end">Frete</td><td class="text-end"><?= moeda($pedido['frete']) ?></td><td></td></tr>
            <tr class="fw-semibold"><td colspan="4" class="text-end">Total</td><td class="text-end"><?= moeda($subtotal + $pedido['frete']) ?></td><td></td></tr>
        </tfoot>
    </table>
</div>
