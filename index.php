<?php
require __DIR__ . '/includes/bootstrap.php';

$destaques = db()->query("SELECT * FROM produtos WHERE ativo = 1 AND destaque = 1 ORDER BY criado_em DESC LIMIT 4")->fetchAll();
$servicos  = db()->query("SELECT * FROM servicos ORDER BY ordem LIMIT 5")->fetchAll();

$pagina = 'inicio';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="wrap hero__in">
    <div>
      <h1 class="hero__titulo"><span>Bike</span><span>for all.</span></h1>
      <p class="hero__texto">Oficina de reparação e loja de bicicletas. Revisões, afinações e peças para estrada, BTT e cidade — com o cuidado de quem também pedala.</p>
      <div class="hero__acoes">
        <a class="btn" href="<?= url('marcar.php') ?>">Marcar reparação</a>
        <a class="btn btn--contorno" href="<?= url('loja.php') ?>">Ver a loja</a>
      </div>
    </div>
    <svg class="hero__bike" viewBox="0 0 520 320" role="img" aria-label="Desenho de uma bicicleta de estrada">
      <defs>
        <linearGradient id="hg" x1="0" x2="1"><stop offset="0" stop-color="#C6F24A"/><stop offset="1" stop-color="#5FAE0B"/></linearGradient>
      </defs>
      <g class="roda">
        <circle cx="120" cy="215" r="88" fill="none" stroke="#fff" stroke-width="7"/>
        <circle cx="120" cy="215" r="78" fill="none" stroke="#3A3F42" stroke-width="2"/>
        <g stroke="#6B7276" stroke-width="1.5"><path d="M120 137v156M42 215h156M65 160l110 110M65 270l110-110M90 143l60 144M150 143l-60 144"/></g>
        <circle cx="120" cy="215" r="8" fill="#fff"/>
      </g>
      <g class="roda">
        <circle cx="400" cy="215" r="88" fill="none" stroke="#fff" stroke-width="7"/>
        <circle cx="400" cy="215" r="78" fill="none" stroke="#3A3F42" stroke-width="2"/>
        <g stroke="#6B7276" stroke-width="1.5"><path d="M400 137v156M322 215h156M345 160l110 110M345 270l110-110M370 143l60 144M430 143l-60 144"/></g>
        <circle cx="400" cy="215" r="8" fill="#fff"/>
      </g>
      <g fill="none" stroke="url(#hg)" stroke-width="11" stroke-linecap="round" stroke-linejoin="round">
        <path d="M120 215 L205 92 L362 100 L250 215 Z"/>
        <path d="M205 92 L250 215"/>
        <path d="M362 100 L400 215"/>
      </g>
      <g fill="none" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M198 72 L218 112"/>
        <path d="M168 66 h58"/>
        <path d="M356 82 L366 112"/>
        <path d="M346 80 h36 c18 0 22 14 18 26 c-3 9 -12 12 -20 8"/>
      </g>
      <circle cx="250" cy="215" r="20" fill="none" stroke="#fff" stroke-width="5"/>
      <path d="M250 215 L238 248 M226 252 h24" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
    </svg>
  </div>
</section>

<section class="seccao seccao--branca">
  <div class="wrap">
    <div class="seccao__topo">
      <h2>Oficina</h2>
      <a href="<?= url('servicos.php') ?>">Tabela de preços completa</a>
    </div>
    <p class="intro">Na manutenção, cada detalhe conta. Quando um rolamento ainda está bom, limpamos e lubrificamos em vez de o trocar — só substituímos o que precisa mesmo.</p>
    <div class="precos">
      <div class="precos__grupo">
        <?php foreach ($servicos as $s): ?>
          <div class="preco-linha">
            <span class="preco-linha__nome"><?= e($s['nome']) ?></span>
            <span class="preco-linha__valor"><?php if ($s['preco_desde']): ?><small>desde</small><?php endif; ?><?= euro($s['preco']) ?></span>
            <?php if ($s['descricao']): ?><span class="preco-linha__desc"><?= e($s['descricao']) ?></span><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div>
        <h3>Como funciona</h3>
        <ol class="passos passos--2">
          <li><strong>Marque</strong><p>Online, por telefone ou WhatsApp.</p></li>
          <li><strong>Deixe a bicicleta</strong><p>Fazemos o diagnóstico na hora.</p></li>
          <li><strong>Aprove o orçamento</strong><p>Nada é feito sem o seu OK.</p></li>
          <li><strong>Levante e pedale</strong><p>Avisamos assim que estiver pronta.</p></li>
        </ol>
      </div>
    </div>
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
