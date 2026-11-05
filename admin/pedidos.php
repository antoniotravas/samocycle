<?php
require __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $estado = $_POST['estado'] ?? '';
    if (in_array($estado, ['novo', 'visto', 'tratado'], true)) {
        db()->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$estado, (int)$_POST['id']]);
    }
    redirect('admin/pedidos.php' . (!empty($_GET['todos']) ? '?todos=1' : ''));
}

$todos = !empty($_GET['todos']);
$lista = db()->query("SELECT * FROM pedidos" . ($todos ? "" : " WHERE estado <> 'tratado'") . " ORDER BY criado_em DESC LIMIT 200")->fetchAll();

admin_header('Marcações e mensagens', 'pedidos');
?>
<section class="seccao">
  <div class="wrap">
    <div class="seccao__topo">
      <h1>Marcações e mensagens</h1>
      <a href="<?= url('admin/pedidos.php' . ($todos ? '' : '?todos=1')) ?>"><?= $todos ? 'Ver só por tratar' : 'Ver também os tratados' ?></a>
    </div>
    <?php if (!$lista): ?>
      <p class="vazio">Nada por tratar.</p>
    <?php else: ?>
      <div class="tabela-scroll">
        <table class="tabela">
          <thead><tr><th>Recebido</th><th>Tipo</th><th>Cliente</th><th>Detalhes</th><th>Estado</th></tr></thead>
          <tbody>
          <?php foreach ($lista as $p): ?>
            <tr>
              <td><?= date('d/m H:i', strtotime($p['criado_em'])) ?></td>
              <td><?= $p['tipo'] === 'reparacao' ? 'Reparação' : 'Mensagem' ?></td>
              <td>
                <strong><?= e($p['nome']) ?></strong><br>
                <?php if ($p['telemovel']): ?><a href="https://wa.me/351<?= e($p['telemovel']) ?>"><?= e($p['telemovel']) ?></a><br><?php endif; ?>
                <?php if ($p['email']): ?><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a><?php endif; ?>
              </td>
              <td>
                <?php if ($p['servico']): ?><strong><?= e($p['servico']) ?></strong><br><?php endif; ?>
                <?php if ($p['bicicleta']): ?>Bicicleta: <?= e($p['bicicleta']) ?><br><?php endif; ?>
                <?php if ($p['data_preferida']): ?>Dia preferido: <?= date('d/m/Y', strtotime($p['data_preferida'])) ?><br><?php endif; ?>
                <?= nl2br(e($p['mensagem'])) ?>
              </td>
              <td>
                <span class="etiqueta etiqueta--<?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span>
                <form method="post" class="acoes-linha" style="margin-top:var(--s2)">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <?php if ($p['estado'] !== 'tratado'): ?><button class="btn btn--pequeno" name="estado" value="tratado">Marcar tratado</button><?php endif; ?>
                  <?php if ($p['estado'] === 'novo'): ?><button class="link-btn" name="estado" value="visto">Visto</button><?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
