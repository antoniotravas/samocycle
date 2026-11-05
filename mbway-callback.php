<?php
// Callback da ifthenpay: configure no backoffice da ifthenpay o URL
// https://SEU-DOMINIO/mbway-callback.php?key=[ANTI_PHISHING_KEY]&orderId=[ORDER_ID]&amount=[AMOUNT]&requestId=[REQUEST_ID]
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/mbway.php';

if (IFTHENPAY_ANTIPHISHING === '' || !hash_equals(IFTHENPAY_ANTIPHISHING, (string)($_GET['key'] ?? ''))) {
    http_response_code(403);
    exit('forbidden');
}

$st = db()->prepare("SELECT id, total FROM encomendas WHERE referencia = ?");
$st->execute([(string)($_GET['orderId'] ?? '')]);
$enc = $st->fetch();

if (!$enc || abs((float)$enc['total'] - (float)($_GET['amount'] ?? -1)) > 0.009) {
    http_response_code(404);
    exit('not found');
}

encomenda_marcar_paga((int)$enc['id']);
echo 'ok';
