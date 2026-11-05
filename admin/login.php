<?php
require __DIR__ . '/../includes/bootstrap.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION['tentativas'] = ($_SESSION['tentativas'] ?? 0) + 1;
    if ($_SESSION['tentativas'] > 8) {
        $erro = 'Demasiadas tentativas. Aguarde alguns minutos e tente de novo.';
    } else {
        $st = db()->prepare("SELECT * FROM admins WHERE utilizador = ?");
        $st->execute([trim($_POST['utilizador'] ?? '')]);
        $a = $st->fetch();
        if ($a && password_verify($_POST['password'] ?? '', $a['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$a['id'];
            unset($_SESSION['tentativas']);
            redirect('admin/');
        }
        $erro = 'Utilizador ou palavra-passe errados.';
    }
}

$titulo = 'Entrar';
require __DIR__ . '/../includes/header.php';
?>
<section class="seccao">
  <div class="wrap" style="max-width:26rem">
    <h1>Área reservada</h1>
    <?php if ($erro): ?><p class="flash flash--erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <form class="form" method="post" style="margin-top:var(--s4)">
      <?= csrf_field() ?>
      <div class="campo"><label for="u">Utilizador</label><input id="u" type="text" name="utilizador" autocomplete="username" required></div>
      <div class="campo"><label for="p">Palavra-passe</label><input id="p" type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn" type="submit">Entrar</button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
