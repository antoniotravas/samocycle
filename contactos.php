<?php
require __DIR__ . '/includes/bootstrap.php';

$d = ['nome' => '', 'telemovel' => '', 'email' => '', 'mensagem' => ''];
$erros = [];
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($d as $k => $_) $d[$k] = trim((string)($_POST[$k] ?? ''));
    if (!empty($_POST['website'])) exit;

    if ($d['nome'] === '') $erros['nome'] = 'Indique o seu nome.';
    $tel = $d['telemovel'] !== '' ? telemovel_valido($d['telemovel']) : null;
    if ($d['telemovel'] !== '' && !$tel) $erros['telemovel'] = 'Telemóvel com 9 dígitos.';
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'O email não parece válido.';
    if (!$tel && $d['email'] === '') $erros['telemovel'] = 'Deixe um telemóvel ou email para lhe respondermos.';
    if ($d['mensagem'] === '') $erros['mensagem'] = 'Escreva a sua mensagem.';

    if (!$erros) {
        db()->prepare("INSERT INTO pedidos (tipo, nome, telemovel, email, mensagem) VALUES ('contacto',?,?,?,?)")
            ->execute([$d['nome'], $tel ?? '', $d['email'] ?: null, $d['mensagem']]);
        $enviado = true;
    }
}

$titulo = 'Contactos';
$pagina = 'contactos';
require __DIR__ . '/includes/header.php';
$tel = str_replace(' ', '', LOJA_TELEFONE);
?>
<header class="cabecalho-pagina">
  <div class="wrap"><h1>Contactos</h1></div>
</header>
<section class="seccao">
  <div class="wrap contactos">
    <div>
      <dl>
        <dt>Telefone e WhatsApp</dt>
        <dd><a href="tel:+351<?= $tel ?>"><?= e(LOJA_TELEFONE) ?></a> · <a href="https://wa.me/<?= LOJA_WHATSAPP ?>">abrir WhatsApp</a><br><span class="ajuda">Chamada para a rede móvel nacional.</span></dd>
        <dt>Email</dt>
        <dd><a href="mailto:<?= e(LOJA_EMAIL) ?>"><?= e(LOJA_EMAIL) ?></a></dd>
        <dt>Oficina</dt>
        <dd><?= e(LOJA_MORADA) ?></dd>
        <dt>Horário</dt>
        <dd>
          <?php foreach (LOJA_HORARIO as $dia => $h): ?><?= e($dia) ?>: <?= e($h) ?><br><?php endforeach; ?>
        </dd>
        <dt>Redes sociais</dt>
        <dd><a href="<?= FACEBOOK_URL ?>" rel="noopener">Facebook</a> · <a href="<?= INSTAGRAM_URL ?>" rel="noopener">Instagram</a></dd>
      </dl>
    </div>
    <div>
      <h2>Envie uma mensagem</h2>
      <?php if ($enviado): ?>
        <p class="flash" role="status">Mensagem enviada. Respondemos no próximo dia útil.</p>
      <?php else: ?>
        <form class="form" method="post" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" class="so-leitores" aria-hidden="true">
          <?php foreach (['nome' => ['Nome', 'text', 'name'], 'telemovel' => ['Telemóvel', 'tel', 'tel-national'], 'email' => ['Email', 'email', 'email']] as $k => [$l, $t, $ac]): ?>
            <div class="campo<?= isset($erros[$k]) ? ' campo--erro' : '' ?>">
              <label for="<?= $k ?>"><?= $l ?></label>
              <input id="<?= $k ?>" type="<?= $t ?>" name="<?= $k ?>" value="<?= e($d[$k]) ?>" autocomplete="<?= $ac ?>"<?= isset($erros[$k]) ? ' aria-invalid="true" aria-describedby="e-' . $k . '"' : '' ?>>
              <?php if (isset($erros[$k])): ?><span class="erro-msg" id="e-<?= $k ?>"><?= e($erros[$k]) ?></span><?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div class="campo<?= isset($erros['mensagem']) ? ' campo--erro' : '' ?>">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem"<?= isset($erros['mensagem']) ? ' aria-invalid="true" aria-describedby="e-mensagem"' : '' ?>><?= e($d['mensagem']) ?></textarea>
            <?php if (isset($erros['mensagem'])): ?><span class="erro-msg" id="e-mensagem"><?= e($erros['mensagem']) ?></span><?php endif; ?>
          </div>
          <div><button class="btn" type="submit">Enviar mensagem</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
