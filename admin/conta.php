<?php
require __DIR__ . '/_auth.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = db()->prepare("SELECT password_hash FROM admins WHERE id = ?");
    $st->execute([$_SESSION['admin_id']]);
    $nova = $_POST['nova'] ?? '';
    if (!password_verify($_POST['atual'] ?? '', (string)$st->fetchColumn())) {
        $erro = 'A palavra-passe atual não está correta.';
    } elseif (strlen($nova) < 10) {
        $erro = 'A nova palavra-passe precisa de pelo menos 10 caracteres.';
    } elseif ($nova !== ($_POST['confirmar'] ?? '')) {
        $erro = 'As duas palavras-passe novas não coincidem.';
    } else {
        db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([password_hash($nova, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        flash('Palavra-passe alterada.');
        redirect('admin/conta.php');
    }
}

admin_header('Conta', 'conta');
?>
<section class="seccao">
  <div class="wrap" style="max-width:28rem">
    <h1>Alterar palavra-passe</h1>
    <?php if ($erro): ?><p class="flash flash--erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <form class="form" method="post" style="margin-top:var(--s4)">
      <?= csrf_field() ?>
      <div class="campo"><label for="a">Palavra-passe atual</label><input id="a" type="password" name="atual" autocomplete="current-password" required></div>
      <div class="campo"><label for="n">Nova palavra-passe</label><input id="n" type="password" name="nova" autocomplete="new-password" minlength="10" required><span class="ajuda">Pelo menos 10 caracteres.</span></div>
      <div class="campo"><label for="c">Repetir a nova</label><input id="c" type="password" name="confirmar" autocomplete="new-password" required></div>
      <button class="btn" type="submit">Alterar palavra-passe</button>
    </form>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
