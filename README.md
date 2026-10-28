# Sistema de Vendas em PHP

CRUD de **clientes, vendedores, produtos e pedidos** em PHP + MySQL, organizado em views e controllers.
Projeto do curso técnico de Desenvolvimento de Sistemas no SENAI Votuporanga (2022).

## Funcionalidades

- Cadastro, alteração, exclusão e listagem de clientes, vendedores e produtos
- Pedidos com data, hora, nota fiscal e frete
- Itens do pedido vinculados a produtos

## Estrutura

```
index.php        tela principal com as abas de cadastro
controllers/     inclusão, alteração e exclusão de cada entidade
views/           estilos e imagens
database.sql     criação do banco vendas_01
```

## Rodando

1. Instalar o XAMPP e iniciar Apache e MySQL
2. Importar `database.sql` no phpMyAdmin
3. Copiar a pasta para `htdocs/sistema-vendas-php`
4. Acessar `http://localhost/sistema-vendas-php`

Conexão padrão: `localhost`, usuário `root`, sem senha.

## O que eu faria diferente hoje

Código de quando eu estava aprendendo. Hoje eu mudaria:

- Consultas com `prepared statements` (PDO), evitando SQL injection
- Conexão com o banco em um único arquivo, não repetida em cada controller
- Tipos corretos no banco (`DATE`, `DECIMAL`) e validação dos campos
- Bootstrap para o layout responsivo

## Stack

PHP · MySQL · HTML · CSS
