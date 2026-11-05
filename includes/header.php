<?php
/** @var string $titulo  @var string $pagina */
$titulo = $titulo ?? LOJA_NOME;
$pagina = $pagina ?? '';
$nav = [
    'inicio'    => ['', 'Início'],
    'servicos'  => ['servicos.php', 'Oficina'],
    'loja'      => ['loja.php', 'Loja'],
    'marcar'    => ['marcar.php', 'Marcar reparação'],
    'contactos' => ['contactos.php', 'Contactos'],
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo === LOJA_NOME ? LOJA_NOME . ' — Bike for all' : $titulo . ' · ' . LOJA_NOME) ?></title>
<meta name="description" content="Samocycle — oficina de reparação de bicicletas e loja de bicicletas, peças e acessórios. Pagamento por MB WAY.">
<link rel="icon" href="<?= url('assets/img/logo.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@1,600;1,800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<a class="skip" href="#conteudo">Saltar para o conteúdo</a>
<header class="topo">
  <div class="wrap topo__in">
    <a class="marca" href="<?= url() ?>" aria-label="Samocycle — página inicial">
      <img src="<?= url('assets/img/logo.svg') ?>" alt="" width="44" height="44">
      <span class="marca__nome">Samocycle</span>
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="nav">Menu</button>
    <nav id="nav" class="nav" aria-label="Principal">
      <?php foreach ($nav as $k => [$href, $label]): ?>
        <a href="<?= url($href) ?>"<?= $pagina === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="nav__cart" href="<?= url('carrinho.php') ?>"<?= $pagina === 'carrinho' ? ' aria-current="page"' : '' ?>>
        Carrinho<?php if ($q = carrinho_qtd()): ?> <span class="badge" aria-label="<?= $q ?> artigos"><?= $q ?></span><?php endif; ?>
      </a>
    </nav>
  </div>
</header>
<main id="conteudo">
<?php foreach (flashes() as $f): ?>
  <div class="wrap"><p class="flash flash--<?= e($f['tipo']) ?>" role="status"><?= e($f['msg']) ?></p></div>
<?php endforeach; ?>
