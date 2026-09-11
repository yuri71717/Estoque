CREATE DATABASE IF NOT EXISTS control_estoque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE control_estoque;

DROP TABLE IF EXISTS ITENS_VENDAS;
DROP TABLE IF EXISTS VENDAS;
DROP TABLE IF EXISTS ITENS_COMPRAS;
DROP TABLE IF EXISTS COMPRAS;
DROP TABLE IF EXISTS PRODUTOS;
DROP TABLE IF EXISTS USUARIOS;
DROP TABLE IF EXISTS FORNECEDORES;
DROP TABLE IF EXISTS CATEGORIAS;

CREATE TABLE CATEGORIAS (
    cd_categoria INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nm_categoria VARCHAR(80) NOT NULL,
    ds_categoria VARCHAR(150)
);

CREATE TABLE FORNECEDORES (
    cd_fornecedor INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nm_fornecedor VARCHAR(80) NOT NULL,
    ds_cnpj VARCHAR(18) NOT NULL,
    ds_telefone VARCHAR(20),
    ds_email VARCHAR(80),
    ds_endereco VARCHAR(120)
);

CREATE TABLE USUARIOS (
    cd_usuario INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nm_usuario VARCHAR(80) NOT NULL,
    ds_login VARCHAR(50) NOT NULL UNIQUE,
    ds_senha VARCHAR(100) NOT NULL,
    ds_email VARCHAR(80),
    ds_tipo VARCHAR(20) NOT NULL
);

CREATE TABLE PRODUTOS (
    cd_produto INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nm_produto VARCHAR(100) NOT NULL,
    ds_produto VARCHAR(200),
    vl_custo DECIMAL(10,2) NOT NULL,
    vl_venda DECIMAL(10,2) NOT NULL,
    qt_estoque INT NOT NULL DEFAULT 0,
    qt_estoque_minimo INT NOT NULL DEFAULT 0,
    cd_categoria INT NOT NULL,
    cd_fornecedor INT NOT NULL,

    FOREIGN KEY (cd_categoria)
        REFERENCES CATEGORIAS(cd_categoria),

    FOREIGN KEY (cd_fornecedor)
        REFERENCES FORNECEDORES(cd_fornecedor)
);

CREATE TABLE COMPRAS (
    cd_compra INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dt_compra DATE NOT NULL,
    vl_total DECIMAL(10,2) NOT NULL,
    cd_fornecedor INT NOT NULL,
    cd_usuario INT NOT NULL,

    FOREIGN KEY (cd_fornecedor)
        REFERENCES FORNECEDORES(cd_fornecedor),

    FOREIGN KEY (cd_usuario)
        REFERENCES USUARIOS(cd_usuario)
);

CREATE TABLE ITENS_COMPRAS (
    cd_item_compra INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cd_compra INT NOT NULL,
    cd_produto INT NOT NULL,
    qt_produto INT NOT NULL,
    vl_unitario DECIMAL(10,2) NOT NULL,
    vl_total DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (cd_compra)
        REFERENCES COMPRAS(cd_compra),

    FOREIGN KEY (cd_produto)
        REFERENCES PRODUTOS(cd_produto)
);

CREATE TABLE VENDAS (
    cd_venda INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dt_venda DATE NOT NULL,
    vl_total DECIMAL(10,2) NOT NULL,
    cd_usuario INT NOT NULL,

    FOREIGN KEY (cd_usuario)
        REFERENCES USUARIOS(cd_usuario)
);

CREATE TABLE ITENS_VENDAS (
    cd_item_venda INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cd_venda INT NOT NULL,
    cd_produto INT NOT NULL,
    qt_produto INT NOT NULL,
    vl_unitario DECIMAL(10,2) NOT NULL,
    vl_total DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (cd_venda)
        REFERENCES VENDAS(cd_venda),

    FOREIGN KEY (cd_produto)
        REFERENCES PRODUTOS(cd_produto)
);

INSERT INTO CATEGORIAS
(nm_categoria, ds_categoria)
VALUES
('Periféricos & Teclados', 'Mouses, teclados e periféricos gamer'),
('Hardware & Peças', 'Placas de vídeo, processadores e placas-mãe'),
('Armazenamento', 'SSDs NVMe, SATA e Discos Rígidos (HD)'),
('Refrigeração & Fans', 'Coolers, Water Coolers e Fans RGB'),
('Monitores & Vídeo', 'Monitores gamer alta taxa de atualização (Hz)'),
('Áudio & Headsets', 'Headsets gamer e microfones');

INSERT INTO FORNECEDORES
(nm_fornecedor, ds_cnpj, ds_telefone, ds_email, ds_endereco)
VALUES
(
    'Tech Distribuidora Gamer',
    '12.345.678/0001-01',
    '(11) 99999-1111',
    'vendas@techgamer.com',
    'Rua Tecnologia, 100'
),
(
    'Mega Peças Informática',
    '23.456.789/0001-02',
    '(11) 98888-2222',
    'contato@megapecas.com',
    'Avenida Brasil, 500'
),
(
    'Digital Hardware Brasil',
    '34.567.890/0001-03',
    '(11) 97777-3333',
    'vendas@digitalhardware.com',
    'Rua Central, 250'
),
(
    'InfoPC Distribuidora',
    '45.678.901/0001-04',
    '(11) 96666-4444',
    'contato@infopc.com',
    'Avenida Informatica, 800'
);

INSERT INTO USUARIOS
(nm_usuario, ds_login, ds_senha, ds_email, ds_tipo)
VALUES
(
    'Administrador',
    'admin',
    '123456',
    'admin@etec.com.br',
    'ADMIN'
),
(
    'Joao Silva',
    'joao',
    '123456',
    'joao@etec.com.br',
    'VENDEDOR'
),
(
    'Maria Souza',
    'maria',
    '123456',
    'maria@etec.com.br',
    'VENDEDOR'
),
(
    'Carlos Oliveira',
    'carlos',
    '123456',
    'carlos@etec.com.br',
    'ESTOQUE'
);

INSERT INTO PRODUTOS
(
    nm_produto,
    ds_produto,
    vl_custo,
    vl_venda,
    qt_estoque,
    qt_estoque_minimo,
    cd_categoria,
    cd_fornecedor
)
VALUES
(
    'Mouse Gamer Logitech G Pro X Superlight',
    'Mouse sem fio ultraleve 25K DPI sensor HERO',
    520.00,
    749.90,
    14,
    3,
    1,
    1
),
(
    'Mouse Gamer Sharkoon X11 RGB',
    'Mouse gamer ergonomico sensor optico 12000 DPI',
    130.00,
    219.90,
    20,
    5,
    1,
    2
),
(
    'Teclado Mecanico Aula F75 RGB Switch Reaper',
    'Teclado compacto 75% conexao tri-mode hot-swap',
    250.00,
    389.90,
    18,
    4,
    1,
    3
),
(
    'Teclado Mecanico Redragon Kumara RGB',
    'Teclado mecanico switch blue ABNT2 com iluminacao RGB',
    145.00,
    239.90,
    22,
    5,
    1,
    3
),
(
    'Monitor Gamer AOC Hero 24" 165Hz 1ms IPS',
    'Monitor gamer 165Hz FreeSync Premium ajuste de altura',
    650.00,
    949.90,
    9,
    2,
    5,
    4
),
(
    'Kit 3 Fans RGB 120mm com Controladora',
    'Kit de ventoinhas 120mm ARGB alto fluxo de ar',
    80.00,
    149.90,
    35,
    6,
    4,
    2
),
(
    'Water Cooler Rise Mode ARGB 240mm',
    'Sistema de water cooling selado 240mm bomba silenciosa',
    190.00,
    299.90,
    11,
    3,
    4,
    1
),
(
    'Placa de Video Geforce RTX 4060 8GB GDDR6',
    'Placa de video com Ray Tracing DLSS 3 dual fan',
    1750.00,
    2299.90,
    6,
    2,
    2,
    1
),
(
    'SSD Kingston NV2 1TB M.2 NVMe (3500MB/s)',
    'SSD PCIe 4.0 NVMe leitura rapida para jogos e SO',
    230.00,
    359.90,
    32,
    8,
    3,
    4
),
(
    'HD Seagate BarraCuda 2TB 7200RPM SATA III',
    'Disco rigido 3.5 polegadas 256MB cache 7200RPM',
    210.00,
    329.90,
    15,
    4,
    3,
    2
),
(
    'Memoria RAM Kingston Fury Beast 16GB DDR4 3200MHz',
    'Modulo de memoria 16GB DDR4 dissipador preto gamer',
    150.00,
    249.90,
    42,
    10,
    2,
    3
),
(
    'Placa Mae ASUS TUF Gaming B550M-Plus',
    'Placa mae socket AM4 DDR4 suporte a Ryzen 5000',
    680.00,
    979.90,
    7,
    2,
    2,
    1
),
(
    'Headset Gamer HyperX Cloud Stinger 2',
    'Headset gamer som espacial DTS drivers 50mm',
    150.00,
    259.90,
    18,
    5,
    6,
    2
);

DELIMITER $$

CREATE TRIGGER TG_VERIFICA_ESTOQUE
BEFORE INSERT ON ITENS_VENDAS
FOR EACH ROW
BEGIN
    DECLARE estoque_atual INT;

    SELECT qt_estoque
    INTO estoque_atual
    FROM PRODUTOS
    WHERE cd_produto = NEW.cd_produto;

    IF estoque_atual < NEW.qt_produto THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Estoque insuficiente para realizar a venda';
    END IF;
END$$

CREATE TRIGGER TG_VENDA_ESTOQUE
AFTER INSERT ON ITENS_VENDAS
FOR EACH ROW
BEGIN
    UPDATE PRODUTOS
    SET qt_estoque = qt_estoque - NEW.qt_produto
    WHERE cd_produto = NEW.cd_produto;
END$$

CREATE TRIGGER TG_COMPRA_ESTOQUE
AFTER INSERT ON ITENS_COMPRAS
FOR EACH ROW
BEGIN
    UPDATE PRODUTOS
    SET qt_estoque = qt_estoque + NEW.qt_produto
    WHERE cd_produto = NEW.cd_produto;
END$$

DELIMITER ;

INSERT INTO COMPRAS
(dt_compra, vl_total, cd_fornecedor, cd_usuario)
VALUES
('2026-09-01', 5200.00, 1, 4),
('2026-09-02', 3750.00, 3, 4),
('2026-09-03', 3500.00, 1, 4),
('2026-09-04', 2300.00, 4, 4);

INSERT INTO ITENS_COMPRAS
(
    cd_compra,
    cd_produto,
    qt_produto,
    vl_unitario,
    vl_total
)
VALUES
(1, 1, 10, 520.00, 5200.00),
(2, 3, 15, 250.00, 3750.00),
(3, 8, 2, 1750.00, 3500.00),
(4, 9, 10, 230.00, 2300.00);

INSERT INTO VENDAS
(dt_venda, vl_total, cd_usuario)
VALUES
('2026-09-05', 2489.60, 2),
('2026-09-06', 749.90, 3),
('2026-09-07', 2299.90, 2),
('2026-09-08', 979.70, 3),
('2026-09-09', 1169.70, 2);

INSERT INTO ITENS_VENDAS
(
    cd_venda,
    cd_produto,
    qt_produto,
    vl_unitario,
    vl_total
)
VALUES
(1, 3, 4, 389.90, 1559.60),
(1, 5, 1, 930.00, 930.00),
(2, 1, 1, 749.90, 749.90),
(3, 8, 1, 2299.90, 2299.90),
(4, 9, 2, 359.90, 719.80),
(4, 11, 1, 259.90, 259.90),
(5, 3, 3, 389.90, 1169.70);
