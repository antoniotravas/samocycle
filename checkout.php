<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/mbway.php';

$itens = carrinho_itens();
if (!$itens) redirect('carrinho.php');
$subtotal = array_sum(array_column($itens, 'subtotal'));

$d = ['nome' => '', 'email' => '', 'telemovel' => '', 'entrega' => 'loja', 'morada' => '', 'codigo_postal' => '', 'localidade' => '', 'notas' => ''];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($d as $k => $_) $d[$k] = trim((string)($_POST[$k] ?? ''));

    if ($d['nome'] === '') $erros['nome'] = 'Indique o seu nome.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'Indique um email válido, para receber a confirmação.';
    $tel = telemovel_valido($d['telemovel']);
    if (!$tel) $erros['telemovel'] = 'Indique o número de telemóvel associado ao MB WAY (9 dígitos).';
    if (!in_array($d['entrega'], ['loja', 'envio'], true)) $d['entrega'] = 'loja';
    if ($d['entrega'] === 'envio') {
        if ($d['morada'] === '') $erros['morada'] = 'Indique a morada de entrega.';
        if (!preg_match('/^\d{4}-\d{3}$/', $d['codigo_postal'])) $erros['codigo_postal'] = 'Use o formato 1234-567.';
        if ($d['localidade'] === '') $erros['localidade'] = 'Indique a localidade.';
    }
    if (empty($_POST['termos'])) $erros['termos'] = 'Aceite as condições de venda para continuar.';

    if (!$erros) {
        $portes = portes($subtotal, $d['entrega']);
        $total  = $subtotal + $portes;
        $pdo    = db();
        $pdo->beginTransaction();
        try {
            // Reserva o stock — falha se alguém comprou entretanto
            $upd = $pdo->prepare("UPDATE produtos SET stock = stock - ? WHERE id = ? AND stock >= ?");
            foreach ($itens as $i) {
                $upd->execute([$i['quantidade'], $i['id'], $i['quantidade']]);
                if ($upd->rowCount() !== 1) throw new RuntimeException('stock');
            }
            $ref = 'SC' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
            $pdo->prepare("INSERT INTO encomendas (referencia, nome, email, telemovel, morada, codigo_postal, localidade, entrega, portes, total, notas)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$ref, $d['nome'], $d['email'], $tel,
                           $d['entrega'] === 'envio' ? $d['morada'] : null,
                           $d['entrega'] === 'envio' ? $d['codigo_postal'] : null,
                           $d['entrega'] === 'envio' ? $d['localidade'] : null,
                           $d['entrega'], $portes, $total, $d['notas'] ?: null]);
            $encId = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare("INSERT INTO encomenda_itens (encomenda_id, produto_id, nome, preco, quantidade) VALUES (?,?,?,?,?)");
            foreach ($itens as $i) $ins->execute([$encId, $i['id'], $i['nome'], $i['preco'], $i['quantidade']]);
            $pdo->commit();
        } catch (RuntimeException) {
            $pdo->rollBack();
            flash('Um dos artigos deixou de ter stock suficiente. Reveja o carrinho.', 'erro');
            redirect('carrinho.php');
        }

        $mb = mbway_pedir(['referencia' => $ref, 'total' => $total, 'telemovel' => $tel, 'email' => $d['email']]);
        if ($mb['ok']) {
            db()->prepare("UPDATE encomendas SET mbway_request_id = ? WHERE id = ?")->execute([$mb['request_id'], $encId]);
        }
        $_SESSION['carrinho'] = [];
        $_SESSION['encomendas'][] = $ref;   // permite ver a página de pagamento
        if (!$mb['ok']) flash($mb['erro'] . ' Pode tentar novamente abaixo.', 'erro');
        redirect('pagamento.php?ref=' . $ref);
    }
}

$portes = portes($subtotal, 'envio');

$titulo = 'Finalizar compra';
$pagina = 'carrinho';
require __DIR__ . '/includes/header.php';

function campo_erro(array $erros, string $k): string {
    return isset($erros[$k]) ? '<span class="erro-msg" id="e-' . $k . '">' . e($erros[$k]) . '</span>' : '';
}
function aria_erro(array $erros, string $k): string {
    return isset($erros[$k]) ? ' aria-invalid="true" aria-describedby="e-' . $k . '"' : '';
}
?>
<section class="seccao">
  <div class="wrap">
    <h1>Finalizar compra</h1>
    <?php if ($erros): ?><p class="flash flash--erro" role="alert">Corrija os campos assinalados.</p><?php endif; ?>
    <div class="duas-col">
      <form class="form form--2" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="campo inteiro<?= isset($erros['nome']) ? ' campo--erro' : '' ?>">
          <label for="nome">Nome</label>
          <input id="nome" type="text" name="nome" value="<?= e($d['nome']) ?>" autocomplete="name" required<?= aria_erro($erros, 'nome') ?>>
          <?= campo_erro($erros, 'nome') ?>
        </div>
        <div class="campo<?= isset($erros['email']) ? ' campo--erro' : '' ?>">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" value="<?= e($d['email']) ?>" autocomplete="email" required<?= aria_erro($erros, 'email') ?>>
          <?= campo_erro($erros, 'email') ?>
        </div>
        <div class="campo<?= isset($erros['telemovel']) ? ' campo--erro' : '' ?>">
          <label for="telemovel">Telemóvel MB WAY</label>
          <input id="telemovel" type="tel" name="telemovel" value="<?= e($d['telemovel']) ?>" autocomplete="tel-national" inputmode="numeric" placeholder="912 345 678" required<?= aria_erro($erros, 'telemovel') ?>>
          <?= campo_erro($erros, 'telemovel') ?>
        </div>

        <fieldset class="campo inteiro">
          <legend>Entrega</legend>
          <label class="opcao"><input type="radio" name="entrega" value="loja"<?= $d['entrega'] === 'loja' ? ' checked' : '' ?>>
            <span><strong>Levantar na oficina</strong><br><span class="ajuda">Grátis. Avisamos quando estiver pronto.</span></span></label>
          <label class="opcao"><input type="radio" name="entrega" value="envio"<?= $d['entrega'] === 'envio' ? ' checked' : '' ?>>
            <span><strong>Envio por CTT</strong><br><span class="ajuda"><?= $portes > 0 ? euro($portes) . ' — grátis acima de ' . euro(PORTES_GRATIS_ACIMA) : 'Grátis nesta encomenda' ?>. 2 a 4 dias úteis.</span></span></label>
        </fieldset>

        <div id="bloco-morada" class="inteiro form form--2" style="max-width:none">
          <div class="campo inteiro<?= isset($erros['morada']) ? ' campo--erro' : '' ?>">
            <label for="morada">Morada</label>
            <input id="morada" type="text" name="morada" value="<?= e($d['morada']) ?>" autocomplete="street-address"<?= aria_erro($erros, 'morada') ?>>
            <?= campo_erro($erros, 'morada') ?>
          </div>
          <div class="campo<?= isset($erros['codigo_postal']) ? ' campo--erro' : '' ?>">
            <label for="codigo_postal">Código postal</label>
            <input id="codigo_postal" type="text" name="codigo_postal" value="<?= e($d['codigo_postal']) ?>" autocomplete="postal-code" placeholder="1234-567"<?= aria_erro($erros, 'codigo_postal') ?>>
            <?= campo_erro($erros, 'codigo_postal') ?>
          </div>
          <div class="campo<?= isset($erros['localidade']) ? ' campo--erro' : '' ?>">
            <label for="localidade">Localidade</label>
            <input id="localidade" type="text" name="localidade" value="<?= e($d['localidade']) ?>" autocomplete="address-level2"<?= aria_erro($erros, 'localidade') ?>>
            <?= campo_erro($erros, 'localidade') ?>
          </div>
        </div>

        <div class="campo inteiro">
          <label for="notas">Notas (opcional)</label>
          <textarea id="notas" name="notas" rows="3"><?= e($d['notas']) ?></textarea>
        </div>
        <div class="campo inteiro<?= isset($erros['termos']) ? ' campo--erro' : '' ?>">
          <label class="opcao" style="margin:0"><input type="checkbox" name="termos" value="1"<?= !empty($_POST['termos']) ? ' checked' : '' ?>>
            <span>Li e aceito as condições de venda, incluindo o direito de devolução em 14 dias.</span></label>
          <?= campo_erro($erros, 'termos') ?>
        </div>
        <div class="inteiro">
          <button class="btn btn--largo" type="submit">Pagar com MB WAY</button>
          <p class="ajuda" style="margin-top:var(--s2)">Vai receber uma notificação na app MB WAY para confirmar o pagamento.</p>
        </div>
      </form>

      <aside class="resumo">
        <h2>A sua encomenda</h2>
        <ul>
          <?php foreach ($itens as $i): ?>
            <li><span><?= (int)$i['quantidade'] ?> × <?= e($i['nome']) ?></span><span><?= euro($i['subtotal']) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <dl id="totais" data-loja="<?= e(euro($subtotal)) ?>" data-envio="<?= e(euro($subtotal + $portes)) ?>"
            data-portes="<?= e($portes > 0 ? euro($portes) : 'Grátis') ?>">
          <dt>Subtotal</dt><dd><?= euro($subtotal) ?></dd>
          <dt>Envio</dt><dd id="v-portes">Grátis</dd>
          <dt class="total">Total</dt><dd class="total" id="v-total"><?= euro($subtotal) ?></dd>
        </dl>
        <p class="mbway"><span class="mbway__logo">MB WAY</span> Pagamento no telemóvel</p>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
