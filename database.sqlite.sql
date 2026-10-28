
CREATE TABLE clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(60) NOT NULL,
    sobrenome VARCHAR(60) NOT NULL,
    cpf CHAR(11) NOT NULL UNIQUE,
    nascimento DATE NULL,
    telefone VARCHAR(20) NULL,
    endereco VARCHAR(120) NULL
);

CREATE TABLE vendedores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(60) NOT NULL,
    sobrenome VARCHAR(60) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    cpf CHAR(11) NOT NULL UNIQUE,
    telefone VARCHAR(20) NULL
);

CREATE TABLE produtos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    descricao VARCHAR(120) NOT NULL,
    fornecedor VARCHAR(120) NULL,
    estoque INT NOT NULL DEFAULT 0,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL
);

CREATE TABLE pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    emissao DATE NOT NULL,
    nota_fiscal VARCHAR(20) NULL,
    frete DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (cliente_id) REFERENCES clientes (id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores (id)
);

CREATE TABLE itens_pedido (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    valor_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos (id),
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
);
