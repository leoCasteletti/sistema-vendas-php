<?php

declare(strict_types=1);

final class Cadastros
{
    public const ENTIDADES = [
        'clientes' => [
            'titulo' => 'Clientes',
            'singular' => 'cliente',
            'campos' => [
                'nome' => ['rotulo' => 'Nome', 'tipo' => 'text', 'obrigatorio' => true],
                'sobrenome' => ['rotulo' => 'Sobrenome', 'tipo' => 'text', 'obrigatorio' => true],
                'cpf' => ['rotulo' => 'CPF', 'tipo' => 'cpf', 'obrigatorio' => true],
                'nascimento' => ['rotulo' => 'Nascimento', 'tipo' => 'date'],
                'telefone' => ['rotulo' => 'Telefone', 'tipo' => 'tel'],
                'endereco' => ['rotulo' => 'Endereço', 'tipo' => 'text'],
            ],
            'ordem' => 'nome, sobrenome',
        ],
        'vendedores' => [
            'titulo' => 'Vendedores',
            'singular' => 'vendedor',
            'campos' => [
                'nome' => ['rotulo' => 'Nome', 'tipo' => 'text', 'obrigatorio' => true],
                'sobrenome' => ['rotulo' => 'Sobrenome', 'tipo' => 'text', 'obrigatorio' => true],
                'email' => ['rotulo' => 'E-mail', 'tipo' => 'email', 'obrigatorio' => true],
                'cpf' => ['rotulo' => 'CPF', 'tipo' => 'cpf', 'obrigatorio' => true],
                'telefone' => ['rotulo' => 'Telefone', 'tipo' => 'tel'],
            ],
            'ordem' => 'nome, sobrenome',
        ],
        'produtos' => [
            'titulo' => 'Produtos',
            'singular' => 'produto',
            'campos' => [
                'codigo' => ['rotulo' => 'Código', 'tipo' => 'text', 'obrigatorio' => true],
                'descricao' => ['rotulo' => 'Descrição', 'tipo' => 'text', 'obrigatorio' => true],
                'fornecedor' => ['rotulo' => 'Fornecedor', 'tipo' => 'text'],
                'estoque' => ['rotulo' => 'Estoque', 'tipo' => 'int', 'obrigatorio' => true],
                'preco_custo' => ['rotulo' => 'Preço de custo', 'tipo' => 'money', 'obrigatorio' => true],
                'preco_venda' => ['rotulo' => 'Preço de venda', 'tipo' => 'money', 'obrigatorio' => true],
            ],
            'ordem' => 'descricao',
        ],
    ];

    public function __construct(private PDO $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $entidade, string $busca = ''): array
    {
        $def = $this->definicao($entidade);
        $sql = "SELECT * FROM $entidade";
        $params = [];

        if ($busca !== '') {
            $condicoes = array_map(fn ($c) => "$c LIKE :busca", array_keys($def['campos']));
            $sql .= ' WHERE ' . implode(' OR ', $condicoes);
            $params['busca'] = '%' . $busca . '%';
        }

        $consulta = $this->db->prepare("$sql ORDER BY {$def['ordem']}");
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(string $entidade, int $id): ?array
    {
        $this->definicao($entidade);
        $consulta = $this->db->prepare("SELECT * FROM $entidade WHERE id = ?");
        $consulta->execute([$id]);

        return $consulta->fetch() ?: null;
    }

    /** @param array<string, mixed> $entrada */
    public function salvar(string $entidade, ?int $id, array $entrada): int
    {
        $dados = $this->validar($entidade, $entrada);
        $colunas = array_keys($dados);

        if ($id === null) {
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $entidade,
                implode(', ', $colunas),
                implode(', ', array_map(fn ($c) => ":$c", $colunas))
            );
            $this->executar($sql, $dados);

            return (int) $this->db->lastInsertId();
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id = :id',
            $entidade,
            implode(', ', array_map(fn ($c) => "$c = :$c", $colunas))
        );
        $this->executar($sql, $dados + ['id' => $id]);

        return $id;
    }

    public function excluir(string $entidade, int $id): void
    {
        $this->definicao($entidade);
        try {
            $this->db->prepare("DELETE FROM $entidade WHERE id = ?")->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException('Não é possível excluir: o registro está ligado a um pedido.');
            }
            throw $e;
        }
    }

    /** @return array<string, mixed> */
    public function definicao(string $entidade): array
    {
        return self::ENTIDADES[$entidade] ?? throw new DomainException('Cadastro inexistente.');
    }

    /** @param array<string, mixed> $entrada @return array<string, mixed> */
    private function validar(string $entidade, array $entrada): array
    {
        $dados = [];
        foreach ($this->definicao($entidade)['campos'] as $campo => $regra) {
            $valor = trim((string) ($entrada[$campo] ?? ''));

            if ($valor === '') {
                if ($regra['obrigatorio'] ?? false) {
                    throw new DomainException("Preencha o campo {$regra['rotulo']}.");
                }
                $dados[$campo] = null;
                continue;
            }

            $dados[$campo] = match ($regra['tipo']) {
                'cpf' => Validacao::cpf($valor),
                'email' => Validacao::email($valor),
                'date' => Validacao::data($valor, $regra['rotulo']),
                'int' => Validacao::inteiro($valor, $regra['rotulo']),
                'money' => Validacao::dinheiro($valor, $regra['rotulo']),
                default => mb_substr($valor, 0, 120),
            };
        }

        return $dados;
    }

    /** @param array<string, mixed> $params */
    private function executar(string $sql, array $params): void
    {
        try {
            $this->db->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException('Já existe um registro com esses dados.');
            }
            throw $e;
        }
    }
}
