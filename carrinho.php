<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id  = (int)($_POST['id'] ?? 0);
    $qtd = max(0, (int)($_POST['qtd'] ?? 1));
    $c   = carrinho();

    switch ($_POST['acao'] ?? '') {
        case 'adicionar':
            $c[$id] = ($c[$id] ?? 0) + max(1, $qtd);
            flash('Adicionado ao carrinho.');
            break;
        case 'atualizar':
            if ($qtd > 0) $c[$id] = $qtd; else unset($c[$id]);
            break;
        case 'remover':
            unset($c[$id]);
            flash('Artigo removido.');
            break;
    }
    $_SESSION['carrinho'] = $c;
    redirect('carrinho.php');
}

$itens    = carrinho_itens();
$subtotal = array_sum(array_column($itens, 'subtotal'));

$titulo = 'Carrinho';
$pagina = 'carrinho';
require __DIR__ . '/includes/header.php';
?>
<section class="seccao">
  <div class="wrap">
    <h1>Carrinho</h1>
    <?php if (!$itens): ?>
      <div class="vazio">
        <p>O seu carrinho está vazio.</p>
        <a class="btn" href="<?= url('loja.php') ?>">Ir para a loja</a>
      </div>
    <?php else: ?>
      <div class="duas-col">
        <table class="carrinho">
          <thead><tr><th>Produto</th><th class="num">Preço</th><th>Quantidade</th><th class="num">Subtotal</th><th><span class="so-leitores">Remover</span></th></tr></thead>
          <tbody>
          <?php foreach ($itens as $i): ?>
            <tr>
              <td>
                <div class="carrinho__prod">
                  <img src="<?= e(produto_img($i)) ?>" alt="" width="64" height="48">
                  <a href="<?= url('produto.php?id=' . (int)$i['id']) ?>"><?= e($i['nome']) ?></a>
                </div>
              </td>
              <td class="num" data-label="Preço"><?= euro($i['preco']) ?></td>
              <td data-label="Qtd.">
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="acao" value="atualizar">
                  <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                  <label class="so-leitores" for="q<?= (int)$i['id'] ?>">Quantidade de <?= e($i['nome']) ?></label>
                  <input id="q<?= (int)$i['id'] ?>" type="number" name="qtd" min="0" max="<?= (int)$i['stock'] ?>" value="<?= (int)$i['quantidade'] ?>" data-auto-submit>
                  <noscript><button class="btn btn--pequeno" type="submit">Atualizar</button></noscript>
                </form>
              </td>
              <td class="num" data-label="Subtotal"><strong><?= euro($i['subtotal']) ?></strong></td>
              <td>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="acao" value="remover">
                  <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                  <button class="link-btn" type="submit">Remover</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <aside class="resumo">
          <h2>Resumo</h2>
          <dl>
            <dt>Subtotal</dt><dd><?= euro($subtotal) ?></dd>
            <dt>Envio</dt><dd>Grátis na oficina · <?= euro(PORTES_ENVIO) ?> por CTT</dd>
          </dl>
          <?php if ($subtotal < PORTES_GRATIS_ACIMA): ?>
            <p class="ajuda">Envio grátis acima de <?= euro(PORTES_GRATIS_ACIMA) ?>.</p>
          <?php endif; ?>
          <a class="btn btn--largo" href="<?= url('checkout.php') ?>">Finalizar compra</a>
          <p style="margin-top:var(--s3)"><a href="<?= url('loja.php') ?>">Continuar a comprar</a></p>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
