<?php
require __DIR__ . '/includes/bootstrap.php';

$grupos = [];
foreach (db()->query("SELECT * FROM servicos ORDER BY ordem") as $s) {
    $grupos[$s['grupo']][] = $s;
}

$titulo = 'Oficina';
$pagina = 'servicos';
require __DIR__ . '/includes/header.php';
?>
<header class="cabecalho-pagina">
  <div class="wrap">
    <h1>Oficina</h1>
    <p class="intro">Preços de mão de obra, com IVA incluído. Peças à parte, sempre com orçamento aprovado por si antes de começarmos.</p>
  </div>
</header>

<section class="seccao">
  <div class="wrap">
    <div class="precos">
      <?php foreach ($grupos as $grupo => $lista): ?>
        <div class="precos__grupo">
          <h3><?= e($grupo) ?></h3>
          <?php foreach ($lista as $s): ?>
            <div class="preco-linha">
              <span class="preco-linha__nome"><?= e($s['nome']) ?></span>
              <span class="preco-linha__valor">
                <?php if ((float)$s['preco'] === 0.0): ?>Grátis
                <?php else: ?><?php if ($s['preco_desde']): ?><small>desde</small><?php endif; ?><?= euro($s['preco']) ?><?php endif; ?>
              </span>
              <?php if ($s['descricao']): ?><span class="preco-linha__desc"><?= e($s['descricao']) ?></span><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="seccao seccao--branca">
  <div class="wrap">
    <h2>Como funciona</h2>
    <ol class="passos">
      <li><strong>Marque</strong><p>Pelo formulário, por telefone ou WhatsApp. Diga-nos o que nota na bicicleta.</p></li>
      <li><strong>Deixe a bicicleta</strong><p>Fazemos o diagnóstico quando a recebemos.</p></li>
      <li><strong>Aprove o orçamento</strong><p>Enviamos o valor por mensagem. Nada é feito sem o seu OK.</p></li>
      <li><strong>Levante e pedale</strong><p>Avisamos assim que estiver pronta, afinada e testada.</p></li>
    </ol>
  </div>
</section>

<section class="faixa">
  <div class="wrap faixa__in">
    <div>
      <h2>A sua bicicleta precisa de atenção?</h2>
      <p>Escolha o dia que lhe dá jeito e confirmamos por mensagem.</p>
    </div>
    <a class="btn" href="<?= url('marcar.php') ?>">Marcar reparação</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
