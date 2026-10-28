<?php
$def = $cadastros->definicao($pagina);
$busca = $_GET['busca'] ?? '';
$editando = isset($_GET['editar']) ? $cadastros->buscar($pagina, (int) $_GET['editar']) : null;
$registros = $cadastros->listar($pagina, $busca);
$tipos = ['cpf' => 'text', 'int' => 'number', 'money' => 'text'];
$exibir = function (array $regra, mixed $valor): string {
    return match ($regra['tipo']) {
        'cpf' => cpf($valor),
        'date' => dataBr($valor),
        'money' => moeda($valor),
        default => (string) $valor,
    };
};
?>
<h1 class="h3 mb-4"><?= e($def['titulo']) ?></h1>

<form method="post" action="?<?= e(http_build_query(array_filter(['p' => $pagina, 'id' => $editando['id'] ?? null, 'editar' => $editando['id'] ?? null]))) ?>" class="card card-body mb-4">
    <?= csrf() ?>
    <h2 class="h5"><?= $editando ? 'Editar ' . e($def['singular']) : 'Novo ' . e($def['singular']) ?></h2>
    <div class="row g-3">
        <?php foreach ($def['campos'] as $campo => $regra): ?>
            <div class="col-md-4">
                <label class="form-label" for="<?= $campo ?>"><?= e($regra['rotulo']) ?></label>
                <input class="form-control" id="<?= $campo ?>" name="<?= $campo ?>"
                       type="<?= $tipos[$regra['tipo']] ?? $regra['tipo'] ?>"
                       <?= $regra['tipo'] === 'int' ? 'min="0"' : '' ?>
                       <?= $regra['tipo'] === 'money' ? 'inputmode="decimal" placeholder="0,00"' : '' ?>
                       <?= ($regra['obrigatorio'] ?? false) ? 'required' : '' ?>
                       value="<?= antigo($campo, $editando[$campo] ?? '') ?>">
            </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-dark" name="acao" value="salvar">Salvar</button>
        <?php if ($editando): ?><a class="btn btn-outline-secondary" href="?p=<?= $pagina ?>">Cancelar</a><?php endif; ?>
    </div>
</form>

<form class="row g-2 mb-3">
    <input type="hidden" name="p" value="<?= e($pagina) ?>">
    <div class="col-md-10"><input class="form-control" name="busca" value="<?= e($busca) ?>" placeholder="Buscar"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-outline-dark">Buscar</button></div>
</form>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
        <tr>
            <?php foreach ($def['campos'] as $regra): ?><th><?= e($regra['rotulo']) ?></th><?php endforeach; ?>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <?php foreach ($def['campos'] as $campo => $regra): ?><td><?= e($exibir($regra, $r[$campo])) ?></td><?php endforeach; ?>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="?p=<?= $pagina ?>&amp;editar=<?= (int) $r['id'] ?>">Editar</a>
                    <form method="post" class="d-inline" action="?p=<?= $pagina ?>">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" name="acao" value="excluir">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($registros === []): ?>
            <tr><td colspan="<?= count($def['campos']) + 1 ?>" class="text-secondary">Nenhum registro.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
