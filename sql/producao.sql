-- Samocycle — instalação em produção (importar no phpMyAdmin DENTRO da base de dados criada no cPanel)
-- ATENÇÃO: apaga e recria as tabelas. Usar só na primeira instalação.

DROP TABLE IF EXISTS encomenda_itens;
DROP TABLE IF EXISTS encomendas;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS servicos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS admins;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  ordem INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  marca VARCHAR(80) NULL,
  descricao TEXT NULL,
  preco DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  imagem VARCHAR(255) NULL,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE servicos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  grupo VARCHAR(80) NOT NULL,
  nome VARCHAR(150) NOT NULL,
  descricao VARCHAR(255) NULL,
  preco DECIMAL(10,2) NULL,
  preco_desde TINYINT(1) NOT NULL DEFAULT 0,
  ordem INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE encomendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referencia VARCHAR(20) NOT NULL UNIQUE,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telemovel VARCHAR(20) NOT NULL,
  morada VARCHAR(255) NULL,
  codigo_postal VARCHAR(10) NULL,
  localidade VARCHAR(80) NULL,
  entrega ENUM('loja','envio') NOT NULL DEFAULT 'loja',
  portes DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  metodo VARCHAR(20) NOT NULL DEFAULT 'mbway',
  estado ENUM('pendente','paga','enviada','concluida','cancelada') NOT NULL DEFAULT 'pendente',
  mbway_request_id VARCHAR(80) NULL,
  notas TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pago_em DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE encomenda_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  encomenda_id INT NOT NULL,
  produto_id INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  quantidade INT NOT NULL,
  FOREIGN KEY (encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('reparacao','contacto') NOT NULL DEFAULT 'reparacao',
  nome VARCHAR(120) NOT NULL,
  telemovel VARCHAR(20) NOT NULL,
  email VARCHAR(150) NULL,
  bicicleta VARCHAR(120) NULL,
  servico VARCHAR(150) NULL,
  data_preferida DATE NULL,
  mensagem TEXT NULL,
  estado ENUM('novo','visto','tratado') NOT NULL DEFAULT 'novo',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilizador VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------- Dados iniciais ----------

INSERT INTO categorias (nome, slug, ordem) VALUES
('Bicicletas', 'bicicletas', 1),
('Peças', 'pecas', 2),
('Rodas e pneus', 'rodas-pneus', 3),
('Acessórios', 'acessorios', 4),
('Manutenção', 'manutencao', 5);

INSERT INTO produtos (categoria_id, nome, marca, descricao, preco, stock, destaque) VALUES
(1, 'Bicicleta de estrada Vitoria Nyx', 'Vitoria Bikes', 'Quadro em carbono, grupo Shimano 105 de 12 velocidades, travões de disco hidráulicos. Montada e afinada na oficina antes da entrega.', 2190.00, 2, 1),
(1, 'Bicicleta de cidade Deed Urban', 'Deed Bikes', 'Quadro em alumínio, 8 velocidades, guarda-lamas e porta-bagagens incluídos. Ideal para o dia a dia.', 649.00, 3, 1),
(1, 'BTT Conor 8500 29"', 'Conor-WRC', 'Suspensão dianteira de 100 mm, transmissão 1x12, rodas 29". Para trilhos e passeios ao fim de semana.', 899.00, 2, 0),
(3, 'Par de rodas Rothar Carbon 45', 'Rothar Wheels', 'Rodas de carbono com perfil de 45 mm, tubeless ready, para travão de disco.', 890.00, 1, 1),
(3, 'Pneu de estrada 700x28c', 'Continental', 'Pneu dobrável com boa aderência em piso molhado.', 42.50, 12, 0),
(3, 'Câmara de ar 29" válvula Presta', 'Schwalbe', 'Câmara de ar para BTT, válvula de 40 mm.', 8.90, 30, 0),
(2, 'Corrente 12 velocidades', 'Shimano', 'Corrente com elo rápido incluído.', 39.90, 8, 0),
(2, 'Pastilhas de travão de disco (par)', 'Shimano', 'Pastilhas de resina, compatíveis com a maioria das pinças Shimano.', 18.50, 20, 1),
(2, 'Cassete 11-34 11 velocidades', 'Shimano', 'Cassete para estrada e gravel.', 54.00, 5, 0),
(4, 'Luzes recarregáveis frente e trás', 'Lezyne', 'Kit de luzes USB-C, 400 lúmens à frente.', 49.90, 10, 1),
(4, 'Capacete de estrada', 'Met', 'Leve e ventilado, ajuste rotativo. Vários tamanhos.', 79.00, 6, 0),
(4, 'Bomba de pé com manómetro', 'Topeak', 'Para válvulas Presta e Schrader, até 11 bar.', 34.90, 7, 0),
(5, 'Lubrificante de corrente para seco', 'Squirt', 'Lubrificante à base de cera, 120 ml.', 12.90, 25, 0),
(5, 'Desengordurante biodegradável', 'Muc-Off', 'Limpa corrente e transmissão sem desmontar, 500 ml.', 14.50, 15, 0);

INSERT INTO servicos (grupo, nome, descricao, preco, preco_desde, ordem) VALUES
('Revisões', 'Revisão básica', 'Afinação de mudanças e travões, aperto geral, pressão dos pneus, lubrificação da corrente.', 30.00, 0, 1),
('Revisões', 'Revisão completa', 'Revisão básica, mais limpeza da transmissão, verificação de rolamentos e centragem de rodas.', 60.00, 0, 2),
('Revisões', 'Revisão total com desmontagem', 'Bicicleta desmontada, limpa peça a peça, rolamentos lubrificados ou substituídos, montada e afinada.', 120.00, 1, 3),
('Rodas', 'Reparar furo', 'Inclui mão de obra. Câmara de ar à parte.', 8.00, 0, 4),
('Rodas', 'Centrar roda', NULL, 15.00, 0, 5),
('Rodas', 'Conversão para tubeless', 'Por roda, líquido selante incluído.', 20.00, 0, 6),
('Rodas', 'Montagem de raios', 'Por raio partido.', 6.00, 1, 7),
('Transmissão e travões', 'Afinar mudanças', NULL, 12.00, 0, 8),
('Transmissão e travões', 'Substituir corrente ou cassete', 'Mão de obra. Peças à parte.', 10.00, 0, 9),
('Transmissão e travões', 'Sangrar travões hidráulicos', 'Por travão, óleo incluído.', 20.00, 0, 10),
('Transmissão e travões', 'Substituir pastilhas', 'Por travão, mão de obra.', 8.00, 0, 11),
('Outros', 'Montagem de bicicleta nova', 'Bicicletas compradas noutro lado.', 40.00, 0, 12),
('Outros', 'Ajuste de posição (bike fit básico)', 'Altura e recuo do selim, guiador e avanço.', 35.00, 0, 13),
('Outros', 'Orçamento e diagnóstico', 'Gratuito quando a reparação é feita na Samocycle.', 0.00, 0, 14);

-- Administrador inicial: utilizador "admin", palavra-passe "samocycle2026"
-- MUDE A PALAVRA-PASSE no primeiro acesso (Admin → Conta).
INSERT INTO admins (utilizador, password_hash) VALUES
('admin', '$2y$10$Lh04McT9xzuzSvKhG5zhWeA2kg2DyLkzI6ym20ICRzEscacaeVqXG');
