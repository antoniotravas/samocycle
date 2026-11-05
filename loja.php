<?php
require __DIR__ . '/includes/bootstrap.php';

$categorias = db()->query("SELECT * FROM categorias ORDER BY ordem")->fetchAll();
$slug  = $_GET['categoria'] ?? '';
$busca = trim($_GET['q'] ?? '');

$sql = "SELECT p.* FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.ativo = 1";
$args = [];
if ($slug !== '') { $sql .= " AND c.slug = ?"; $args[] = $slug; }
if ($busca !== '') { $sql .= " AND (p.nome LIKE ? OR p.marca LIKE ?)"; $args[] = "%$busca%"; $args[] = "%$busca%"; }
$sql .= " ORDER BY p.stock > 0 DESC, p.destaque DESC, p.nome";
$st = db()->prepare($sql);
$st->execute($args);
$produtos = $st->fetchAll();

$titulo = 'Loja';
$pagina = 'loja';
require __DIR__ . '/includes/header.php';
?>
<header class="cabecalho-pagina">
  <div class="wrap">
    <h1>Loja</h1>
    <p class="intro">Bicicletas, peças e acessórios. Pague por MB WAY e levante na oficina ou receba em casa.</p>
  </div>
</header>

<section class="seccao">
  <div class="wrap">
    <nav class="filtros" aria-label="Categorias">
      <a href="<?= url('loja.php') ?>" aria-current="<?= $slug === '' ? 'true' : 'false' ?>">Tudo</a>
      <?php foreach ($categorias as $c): ?>
        <a href="<?= url('loja.php?categoria=' . urlencode($c['slug'])) ?>" aria-current="<?= $slug === $c['slug'] ? 'true' : 'false' ?>"><?= e($c['nome']) ?></a>
      <?php endforeach; ?>
    </nav>
    <form class="campo" method="get" style="max-width:24rem;margin-bottom:var(--s5)">
      <?php if ($slug): ?><input type="hidden" name="categoria" value="<?= e($slug) ?>"><?php endif; ?>
      <label for="q">Procurar</label>
      <input id="q" type="text" name="q" value="<?= e($busca) ?>" placeholder="Ex.: corrente, Shimano">
    </form>

    <?php if (!$produtos): ?>
      <div class="vazio">
        <p>Não encontrámos produtos<?= $busca ? ' para “' . e($busca) . '”' : ' nesta categoria' ?>.</p>
        <p>Procura algo específico? <a href="https://wa.me/<?= LOJA_WHATSAPP ?>">Pergunte-nos no WhatsApp</a> — encomendamos.</p>
      </div>
    <?php else: ?>
      <div class="grelha">
        <?php foreach ($produtos as $p) include __DIR__ . '/includes/produto-card.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
