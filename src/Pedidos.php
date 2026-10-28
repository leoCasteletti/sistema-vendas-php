<?php

declare(strict_types=1);

final class Pedidos
{
    public function __construct(private PDO $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return $this->db->query(
            'SELECT p.*, c.nome AS cliente_nome, c.sobrenome AS cliente_sobrenome, v.nome AS vendedor_nome, v.sobrenome AS vendedor_sobrenome,
                    COALESCE((SELECT SUM(i.quantidade * i.valor_unitario) FROM itens_pedido i WHERE i.pedido_id = p.id), 0) + COALESCE(p.frete, 0) AS total
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             JOIN vendedores v ON v.id = p.vendedor_id
             ORDER BY p.emissao DESC, p.id DESC'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare(
            'SELECT p.*, c.nome AS cliente_nome, c.sobrenome AS cliente_sobrenome, v.nome AS vendedor_nome, v.sobrenome AS vendedor_sobrenome
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             JOIN vendedores v ON v.id = p.vendedor_id
             WHERE p.id = ?'
        );
        $consulta->execute([$id]);

        return $consulta->fetch() ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function itens(int $pedidoId): array
    {
        $consulta = $this->db->prepare(
            'SELECT i.*, pr.codigo, pr.descricao, i.quantidade * i.valor_unitario AS subtotal
             FROM itens_pedido i JOIN produtos pr ON pr.id = i.produto_id
             WHERE i.pedido_id = ? ORDER BY i.id'
        );
        $consulta->execute([$pedidoId]);

        return $consulta->fetchAll();
    }

    /** @param array<string, mixed> $d */
    public function criar(array $d): int
    {
        $this->exigir($d, ['cliente_id' => 'cliente', 'vendedor_id' => 'vendedor', 'emissao' => 'data de emissão']);

        $this->db->prepare(
            'INSERT INTO pedidos (cliente_id, vendedor_id, emissao, nota_fiscal, frete) VALUES (?, ?, ?, ?, ?)'
        )->execute([
            (int) $d['cliente_id'],
            (int) $d['vendedor_id'],
            Validacao::data((string) $d['emissao'], 'emissão'),
            trim((string) ($d['nota_fiscal'] ?? '')) ?: null,
            ($d['frete'] ?? '') === '' ? '0.00' : Validacao::dinheiro((string) $d['frete'], 'frete'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function adicionarItem(int $pedidoId, int $produtoId, int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new DomainException('Informe uma quantidade maior que zero.');
        }

        $this->transacao(function () use ($pedidoId, $produtoId, $quantidade) {
            $produto = $this->db->prepare('SELECT estoque, preco_venda, descricao FROM produtos WHERE id = ?');
            $produto->execute([$produtoId]);
            $p = $produto->fetch() ?: throw new DomainException('Produto não encontrado.');

            if ($p['estoque'] < $quantidade) {
                throw new DomainException("Estoque insuficiente de {$p['descricao']}: há {$p['estoque']}.");
            }

            $this->db->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ?')->execute([$quantidade, $produtoId]);
            $this->db->prepare('INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, valor_unitario) VALUES (?, ?, ?, ?)')
                ->execute([$pedidoId, $produtoId, $quantidade, $p['preco_venda']]);
        });
    }

    public function removerItem(int $itemId): void
    {
        $this->transacao(function () use ($itemId) {
            $item = $this->db->prepare('SELECT produto_id, quantidade FROM itens_pedido WHERE id = ?');
            $item->execute([$itemId]);
            $i = $item->fetch() ?: throw new DomainException('Item não encontrado.');

            $this->db->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?')->execute([$i['quantidade'], $i['produto_id']]);
            $this->db->prepare('DELETE FROM itens_pedido WHERE id = ?')->execute([$itemId]);
        });
    }

    public function excluir(int $id): void
    {
        $this->transacao(function () use ($id) {
            foreach ($this->itens($id) as $item) {
                $this->db->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?')->execute([$item['quantidade'], $item['produto_id']]);
            }
            $this->db->prepare('DELETE FROM itens_pedido WHERE pedido_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM pedidos WHERE id = ?')->execute([$id]);
        });
    }

    private function transacao(callable $operacao): void
    {
        $this->db->beginTransaction();
        try {
            $operacao();
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** @param array<string, mixed> $d @param array<string, string> $campos */
    private function exigir(array $d, array $campos): void
    {
        foreach ($campos as $campo => $rotulo) {
            if (trim((string) ($d[$campo] ?? '')) === '') {
                throw new DomainException("Informe $rotulo.");
            }
        }
    }
}
