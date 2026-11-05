<?php
// Integração MB WAY. Ver MBWAY_MODO em config/config.php.

/**
 * Pede o pagamento MB WAY ao telemóvel do cliente.
 * Devolve ['ok' => bool, 'request_id' => ?string, 'erro' => ?string]
 */
function mbway_pedir(array $encomenda): array
{
    if (MBWAY_MODO !== 'ifthenpay') {
        return ['ok' => true, 'request_id' => 'LOCAL-' . $encomenda['referencia'], 'erro' => null];
    }

    $payload = json_encode([
        'mbWayKey'     => IFTHENPAY_MBWAY_KEY,
        'orderId'      => $encomenda['referencia'],
        'amount'       => number_format((float)$encomenda['total'], 2, '.', ''),
        'mobileNumber' => '351#' . $encomenda['telemovel'],
        'email'        => $encomenda['email'],
        'description'  => 'Samocycle ' . $encomenda['referencia'],
    ]);

    $ch = curl_init('https://api.ifthenpay.com/spg/payment/mbway');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        return ['ok' => false, 'request_id' => null, 'erro' => 'Sem ligação ao serviço MB WAY: ' . $err];
    }
    $data = json_decode($resp, true) ?: [];
    if (($data['Status'] ?? '') === '000') {
        return ['ok' => true, 'request_id' => $data['RequestId'] ?? null, 'erro' => null];
    }
    return ['ok' => false, 'request_id' => null, 'erro' => $data['Message'] ?? 'O pedido MB WAY foi recusado.'];
}

/**
 * Consulta o estado de um pedido MB WAY.
 * Devolve 'paga', 'pendente' ou 'recusada'.
 */
function mbway_estado(string $requestId): string
{
    if (MBWAY_MODO !== 'ifthenpay') return 'pendente';

    $q  = http_build_query(['mbWayKey' => IFTHENPAY_MBWAY_KEY, 'requestId' => $requestId]);
    $ch = curl_init('https://api.ifthenpay.com/spg/payment/mbway/status?' . $q);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    $resp = curl_exec($ch);
    curl_close($ch);

    $status = (json_decode((string)$resp, true) ?: [])['Status'] ?? '';
    return match ($status) {
        '000'          => 'paga',
        '020', '101', '122' => 'recusada',
        default        => 'pendente',
    };
}

function encomenda_marcar_paga(int $id): void
{
    db()->prepare("UPDATE encomendas SET estado = 'paga', pago_em = NOW() WHERE id = ? AND estado = 'pendente'")
        ->execute([$id]);
}
