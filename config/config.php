<?php
// Configuração do site Samocycle.
// No servidor, crie config/config.local.php (ver config.local.example.php) — os valores aí definidos
// substituem os de baixo. Assim este ficheiro pode ser igual em local e em produção.

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

$cfg = [
    'MODO_PRODUCAO' => false,

    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'samocycle',
    'DB_USER' => 'root',
    'DB_PASS' => '',

    // URL base (sem barra final). Local: '/samocycle'. Na raiz de um domínio: ''
    'BASE_URL' => '/samocycle',

    'LOJA_NOME'     => 'Samocycle',
    'LOJA_SLOGAN'   => 'Bike for all',
    'LOJA_TELEFONE' => '912 586 949',
    'LOJA_WHATSAPP' => '351912586949',
    'LOJA_EMAIL'    => 'geral@samocycle.pt',   // confirmar
    'LOJA_MORADA'   => 'Morada da oficina',    // preencher
    'LOJA_HORARIO'  => [
        'Segunda a sexta' => '9h30 – 13h00 · 14h30 – 19h00',
        'Sábado'          => '9h30 – 13h00',
        'Domingo'         => 'Fechado',
    ],
    'FACEBOOK_URL'  => 'https://www.facebook.com/samocycle',
    'INSTAGRAM_URL' => 'https://www.instagram.com/samocycle',

    // Portes de envio (CTT). Grátis acima de PORTES_GRATIS_ACIMA.
    'PORTES_ENVIO'        => 4.90,
    'PORTES_GRATIS_ACIMA' => 60.00,

    /*
     * MB WAY
     *  'simulacao' — testes locais: a página de pagamento mostra um botão que simula a aprovação.
     *  'manual'    — sem gateway: o cliente envia o MB WAY para LOJA_MBWAY_NUMERO e o admin marca como paga.
     *  'ifthenpay' — pagamento automático via ifthenpay (precisa de contrato e de uma chave MB WAY).
     */
    'MBWAY_MODO'             => 'simulacao',
    'LOJA_MBWAY_NUMERO'      => '912 586 949',
    'IFTHENPAY_MBWAY_KEY'    => '',
    'IFTHENPAY_ANTIPHISHING' => '',
];

foreach ($cfg as $k => $v) {
    if (!defined($k)) define($k, $v);
}
unset($cfg);

date_default_timezone_set('Europe/Lisbon');
