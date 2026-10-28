CREATE DATABASE IF NOT EXISTS vendas_01 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vendas_01;

CREATE TABLE cadastroclientes (
    Cliente_ID INT NOT NULL AUTO_INCREMENT,
    Cliente_Nome VARCHAR(45),
    Cliente_Sobrenome VARCHAR(45),
    Cliente_RG VARCHAR(20),
    Cliente_CPF VARCHAR(14),
    Cliente_DataNascimento VARCHAR(10),
    Cliente_Genero VARCHAR(20),
    Cliente_Endereco VARCHAR(100),
    Cliente_Telefone VARCHAR(20),
    PRIMARY KEY (Cliente_ID)
);

CREATE TABLE cadastrovendedores (
    Vendedor_ID INT NOT NULL AUTO_INCREMENT,
    Vendedor_Nome VARCHAR(45),
    Vendedor_Sobrenome VARCHAR(45),
    Vendedor_Email VARCHAR(100),
    Vendedor_DataNascimento VARCHAR(10),
    Vendedor_CPF VARCHAR(14),
    Vendedor_RG VARCHAR(20),
    Vendedor_Endereco VARCHAR(100),
    Vendedor_Telefone VARCHAR(20),
    PRIMARY KEY (Vendedor_ID)
);

CREATE TABLE cadastroprodutos (
    Produto_ID INT NOT NULL AUTO_INCREMENT,
    Produto_Codigo VARCHAR(45),
    Produto_Fornecedor VARCHAR(45),
    Produto_Descricao VARCHAR(100),
    Produto_Estoque VARCHAR(20),
    Produto_PrecoCusto VARCHAR(20),
    Produto_PrecoVenda VARCHAR(20),
    PRIMARY KEY (Produto_ID)
);

CREATE TABLE cadastropedidos (
    Pedido_ID INT NOT NULL AUTO_INCREMENT,
    Pedido_DataEmissao VARCHAR(10),
    Pedido_HoraEmissao TIME,
    Pedido_NotaFiscal VARCHAR(20),
    Pedido_DataSaida VARCHAR(10),
    Pedido_HoraSaida TIME,
    Pedido_ValorFrete VARCHAR(20),
    PRIMARY KEY (Pedido_ID)
);

CREATE TABLE itenspedido (
    ItensPedido_ID INT NOT NULL AUTO_INCREMENT,
    Produto_ID INT,
    Pedido_ID INT,
    Quantidade VARCHAR(20),
    ValorUnitario VARCHAR(20),
    PRIMARY KEY (ItensPedido_ID),
    FOREIGN KEY (Produto_ID) REFERENCES cadastroprodutos (Produto_ID),
    FOREIGN KEY (Pedido_ID) REFERENCES cadastropedidos (Pedido_ID)
);
