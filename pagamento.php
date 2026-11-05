<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/mbway.php';

$ref = (string)($_GET['ref'] ?? '');
$autorizado = in_array($ref, $_SESSION['encomendas'] ?? [], true) || !empty($_SESSION['admin_id']);
$st = db()->prepare("SELECT * FROM encomendas WHERE referencia = ?");
$st->execute([$ref]);
$enc = $st->fetch();
if (!$enc || !$autorizado) {
    http_response_code(404);
    exit('Encomenda não encontrada.');
}

// Consulta de estado (usado pela página enquanto espera)
if (isset($_GET['json'])) {
    if ($enc['estado'] === 'pendente' && $enc['mbway_request_id'] && mbway_estado($enc['mbway_request_id']) === 'paga') {
        encomenda_marcar_paga((int)$enc['id']);
        $enc['estado'] = 'paga';
    }
    header('Content-Type: application/json');
    echo json_encode(['estado' => $enc['estado']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $enc['estado'] === 'pendente') {
    csrf_check();
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'simular' && MBWAY_MODO === 'simulacao') {
        encomenda_marcar_paga((int)$enc['id']);
        flash('Pagamento simulado com sucesso.');
    } elseif ($acao === 'reenviar') {
        $tel = telemovel_valido((string)($_POST['telemovel'] ?? $enc['telemovel']));
        if (!$tel) {
            flash('Número de telemóvel inválido.', 'erro');
        } else {
            $mb = mbway_pedir(['referencia' => $enc['referencia'], 'total' => $enc['total'], 'telemovel' => $tel, 'email' => $enc['email']]);
            if ($mb['ok']) {
                db()->prepare("UPDATE encomendas SET telemovel = ?, mbway_request_id = ? WHERE id = ?")->execute([$tel, $mb['request_id'], $enc['id']]);
                flash('Enviámos um novo pedido MB WAY para ' . $tel . '.');
            } else {
                flash($mb['erro'], 'erro');
            }
        }
    }
    redirect('pagamento.php?ref=' . urlencode($ref));
}

$itens = db()->prepare("SELECT * FROM encomenda_itens WHERE encomenda_id = ?");
$itens->execute([$enc['id']]);
$itens = $itens->fetchAll();
$telVisivel = substr($enc['telemovel'], 0, 3) . ' ••• ' . substr($enc['telemovel'], -3);

$titulo = 'Encomenda ' . $enc['referencia'];
require __DIR__ . '/includes/header.php';
?>
<section class="seccao">
  <div class="wrap duas-col">
    <div>
      <?php if ($enc['estado'] === 'pendente'): ?>
        <div data-estado-url="<?= url('pagamento.php?json=1&ref=' . urlencode($ref)) ?>">
          <h1>Confirme no seu telemóvel</h1>
          <?php if (MBWAY_MODO === 'manual'): ?>
            <p class="intro">Envie <strong><?= euro($enc['total']) ?></strong> por MB WAY para o número <strong><?= e(LOJA_MBWAY_NUMERO) ?></strong>, com a referência <span class="ref"><?= e($enc['referencia']) ?></span> na mensagem.</p>
            <p>Confirmamos a encomenda assim que recebermos o pagamento — normalmente no próprio dia.</p>
          <?php else: ?>
            <p class="intro">Enviámos um pedido de <strong><?= euro($enc['total']) ?></strong> para o MB WAY do número <?= e($telVisivel) ?>. Abra a app e aceite o pagamento. Tem 4 minutos.</p>
            <p><span class="pulso" aria-hidden="true"></span>À espera da confirmação…</p>
            <details style="margin-top:var(--s5)">
              <summary>Não recebeu a notificação?</summary>
              <form class="form" method="post" style="margin-top:var(--s3);max-width:24rem">
                <?= csrf_field() ?>
                <input type="hidden" name="acao" value="reenviar">
                <div class="campo">
                  <label for="tel">Telemóvel MB WAY</label>
                  <input id="tel" type="tel" name="telemovel" value="<?= e($enc['telemovel']) ?>" inputmode="numeric">
                </div>
                <button class="btn btn--escuro" type="submit">Enviar novo pedido</button>
              </form>
            </details>
          <?php endif; ?>
          <?php if (MBWAY_MODO === 'simulacao'): ?>
            <form method="post" style="margin-top:var(--s6);padding:var(--s4);border:1px dashed #AEB5AC;border-radius:var(--raio)">
              <?= csrf_field() ?>
              <input type="hidden" name="acao" value="simular">
              <p class="ajuda">Modo de testes: não há pagamento real.</p>
              <button class="btn" type="submit">Simular pagamento aceite</button>
            </form>
          <?php endif; ?>
        </div>
      <?php elseif ($enc['estado'] === 'cancelada'): ?>
        <h1>Encomenda cancelada</h1>
        <p>Esta encomenda foi cancelada. Se foi engano, fale connosco pelo <?= e(LOJA_TELEFONE) ?>.</p>
      <?php else: ?>
        <h1>Obrigado, <?= e(explode(' ', $enc['nome'])[0]) ?>!</h1>
        <p class="intro">Recebemos o pagamento da encomenda <span class="ref"><?= e($enc['referencia']) ?></span>.</p>
        <p><?= $enc['entrega'] === 'envio'
            ? 'Vamos preparar e enviar por CTT para ' . e($enc['localidade']) . '. Enviamos o código de seguimento por email.'
            : 'Avisamos por mensagem quando estiver pronta para levantar na oficina.' ?></p>
        <a class="btn" href="<?= url('loja.php') ?>">Voltar à loja</a>
      <?php endif; ?>
    </div>

    <aside class="resumo">
      <h2>Encomenda <?= e($enc['referencia']) ?></h2>
      <ul>
        <?php foreach ($itens as $i): ?>
          <li><span><?= (int)$i['quantidade'] ?> × <?= e($i['nome']) ?></span><span><?= euro($i['preco'] * $i['quantidade']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <dl>
        <dt>Envio</dt><dd><?= $enc['entrega'] === 'loja' ? 'Levantar na oficina' : ((float)$enc['portes'] > 0 ? euro($enc['portes']) : 'Grátis') ?></dd>
        <dt>Estado</dt><dd><?= e(estado_label($enc['estado'])) ?></dd>
        <dt class="total">Total</dt><dd class="total"><?= euro($enc['total']) ?></dd>
      </dl>
      <p class="mbway"><span class="mbway__logo">MB WAY</span></p>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
