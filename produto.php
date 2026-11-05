<?php
require __DIR__ . '/includes/bootstrap.php';

$st = db()->prepare("SELECT p.*, c.nome AS categoria, c.slug FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.ativo = 1");
$st->execute([(int)($_GET['id'] ?? 0)]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    $titulo = 'Produto não encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<section class="seccao"><div class="wrap vazio"><p>Este produto já não está disponível.</p><a class="btn" href="' . url('loja.php') . '">Voltar à loja</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$titulo = $p['nome'];
$pagina = 'loja';
require __DIR__ . '/includes/header.php';
?>
<section class="seccao">
  <div class="wrap">
    <p><a href="<?= url('loja.php?categoria=' . urlencode($p['slug'])) ?>">← <?= e($p['categoria']) ?></a></p>
    <div class="detalhe">
      <img class="detalhe__img" src="<?= e(produto_img($p)) ?>" alt="<?= e($p['nome']) ?>" width="800" height="600">
      <div>
        <?php if ($p['marca']): ?><p class="produto__marca"><?= e($p['marca']) ?></p><?php endif; ?>
        <h1><?= e($p['nome']) ?></h1>
        <p class="detalhe__preco"><?= euro($p['preco']) ?></p>
        <?php if ((int)$p['stock'] > 0): ?>
          <p class="detalhe__stock"><?= (int)$p['stock'] <= 2 ? 'Últimas unidades em stock' : 'Em stock' ?></p>
          <form class="form" method="post" action="<?= url('carrinho.php') ?>" style="grid-template-columns:auto 1fr;align-items:end;max-width:26rem">
            <?= csrf_field() ?>
            <input type="hidden" name="acao" value="adicionar">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <div class="campo">
              <label for="qtd">Quantidade</label>
              <input id="qtd" type="number" name="qtd" value="1" min="1" max="<?= (int)$p['stock'] ?>" style="width:5.5rem">
            </div>
            <button class="btn" type="submit">Adicionar ao carrinho</button>
          </form>
        <?php else: ?>
          <p class="esgotado">Esgotado de momento.</p>
          <a class="btn btn--escuro" href="https://wa.me/<?= LOJA_WHATSAPP ?>?text=<?= rawurlencode('Olá! Quando volta a ter: ' . $p['nome'] . '?') ?>">Perguntar disponibilidade</a>
        <?php endif; ?>
        <div style="margin-top:var(--s6)">
          <?= nl2br(e($p['descricao'])) ?>
        </div>
        <p class="mbway" style="margin-top:var(--s5)"><span class="mbway__logo">MB WAY</span> Pagamento seguro no seu telemóvel</p>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
