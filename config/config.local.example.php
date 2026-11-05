<?php
// Copie este ficheiro para config.local.php NO SERVIDOR e preencha.
// Só precisa de definir o que é diferente de config.php.

define('MODO_PRODUCAO', true);

// Dados da base de dados criada no cPanel (o nome e o utilizador costumam ter um prefixo, ex.: conta_samocycle)
define('DB_HOST', 'localhost');
define('DB_NAME', 'conta_samocycle');
define('DB_USER', 'conta_samocycle');
define('DB_PASS', 'palavra-passe-da-base-de-dados');

// Site na raiz do domínio (https://samocycle.pt)
define('BASE_URL', '');

define('LOJA_EMAIL', 'geral@samocycle.pt');
define('LOJA_MORADA', 'Rua …, 0000-000 Localidade');

// Até a ifthenpay estar ativa: 'manual'. Depois: 'ifthenpay' + chaves.
define('MBWAY_MODO', 'manual');
// define('IFTHENPAY_MBWAY_KEY', 'ABC-123456');
// define('IFTHENPAY_ANTIPHISHING', 'uma-chave-longa-e-aleatoria');
