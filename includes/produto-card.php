<?php /** @var array $p */ ?>
<article class="produto">
  <img class="produto__img" src="<?= e(produto_img($p)) ?>" alt="" loading="lazy" width="400" height="300">
  <div class="produto__corpo">
    <?php if ($p['marca']): ?><p class="produto__marca"><?= e($p['marca']) ?></p><?php endif; ?>
    <h3 class="produto__nome"><a href="<?= url('produto.php?id=' . (int)$p['id']) ?>"><?= e($p['nome']) ?></a></h3>
    <?php if ((int)$p['stock'] < 1): ?><span class="esgotado">Esgotado</span><?php endif; ?>
    <p class="produto__preco"><?= euro($p['preco']) ?></p>
  </div>
</article>
