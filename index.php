<?php
require __DIR__ . '/includes/bootstrap.php';

$destaques = db()->query("SELECT * FROM produtos WHERE ativo = 1 AND destaque = 1 ORDER BY criado_em DESC LIMIT 4")->fetchAll();
$servicos  = db()->query("SELECT * FROM servicos ORDER BY ordem LIMIT 5")->fetchAll();

$pagina = 'inicio';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <img class="hero__foto" src="<?= url('assets/img/fotos/hero-1672.webp') ?>"
       srcset="<?= url('assets/img/fotos/hero-960.webp') ?> 960w, <?= url('assets/img/fotos/hero-1672.webp') ?> 1672w"
       sizes="100vw" width="1672" height="941" alt="" fetchpriority="high">
  <div class="wrap hero__in">
    <div>
      <h1 class="hero__titulo"><span>Bike</span><span>for all.</span></h1>
      <p class="hero__texto">Oficina de reparação e loja de bicicletas. Revisões, afinações e peças para estrada, BTT e cidade — com o cuidado de quem também pedala.</p>
      <div class="hero__acoes">
        <a class="btn" href="<?= url('marcar.php') ?>">Marcar reparação</a>
        <a class="btn btn--contorno" href="<?= url('loja.php') ?>">Ver a loja</a>
      </div>
    </div>
  </div>
</section>

<section class="seccao seccao--branca">
  <div class="wrap">
    <div class="seccao__topo">
      <h2>Oficina</h2>
      <a href="<?= url('servicos.php') ?>">Tabela de preços completa</a>
    </div>
    <div class="destaque-foto destaque-foto--topo">
      <div>
        <p class="intro">Na manutenção, cada detalhe conta. Quando um rolamento ainda está bom, limpamos e lubrificamos em vez de o trocar — só substituímos o que precisa mesmo.</p>
        <div class="precos__grupo">
          <?php foreach ($servicos as $s): ?>
            <div class="preco-linha">
              <span class="preco-linha__nome"><?= e($s['nome']) ?></span>
              <span class="preco-linha__valor"><?php if ($s['preco_desde']): ?><small>desde</small><?php endif; ?><?= euro($s['preco']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <img class="foto" src="<?= url('assets/img/fotos/oficina-1200.webp') ?>"
           srcset="<?= url('assets/img/fotos/oficina-700.webp') ?> 700w, <?= url('assets/img/fotos/oficina-1200.webp') ?> 1200w"
           sizes="(min-width: 56rem) 34rem, 100vw" width="1200" height="900" loading="lazy"
           alt="Mãos de um mecânico a limpar um rolamento de roda com um pano verde">
    </div>

    <h3>Como funciona</h3>
    <ol class="passos passos--fotos">
      <li><img src="<?= url('assets/img/fotos/passo-marcar-600.webp') ?>" width="600" height="600" loading="lazy" alt=""><strong>Marque</strong><p>Online, por telefone ou WhatsApp.</p></li>
      <li><img src="<?= url('assets/img/fotos/passo-deixar-600.webp') ?>" width="600" height="600" loading="lazy" alt=""><strong>Deixe a bicicleta</strong><p>Fazemos o diagnóstico na hora.</p></li>
      <li><img src="<?= url('assets/img/fotos/passo-diagnostico-600.webp') ?>" width="600" height="600" loading="lazy" alt=""><strong>Aprove o orçamento</strong><p>Nada é feito sem o seu OK.</p></li>
      <li><img src="<?= url('assets/img/fotos/passo-levantar-600.webp') ?>" width="600" height="600" loading="lazy" alt=""><strong>Levante e pedale</strong><p>Avisamos assim que estiver pronta.</p></li>
    </ol>
  </div>
</section>

<section class="seccao">
  <div class="wrap">
    <div class="seccao__topo">
      <h2>Na loja</h2>
      <a href="<?= url('loja.php') ?>">Ver todos os produtos</a>
    </div>
    <div class="grelha">
      <?php foreach ($destaques as $p) include __DIR__ . '/includes/produto-card.php'; ?>
    </div>
  </div>
</section>

<section class="seccao seccao--branca">
  <div class="wrap">
    <h2>Marcas que vendemos</h2>
    <ul class="marcas">
      <li>Vitoria Bikes</li><li>Deed Bikes</li><li>Conor-WRC</li><li>Rothar Wheels</li>
    </ul>
  </div>
</section>

<section class="faixa">
  <div class="wrap faixa__in">
    <div>
      <h2>Pague por MB WAY</h2>
      <p>Encomende online e confirme o pagamento no telemóvel. Levante na oficina ou receba em casa.</p>
    </div>
    <a class="btn btn--contorno" href="https://wa.me/<?= LOJA_WHATSAPP ?>">Falar no WhatsApp</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
