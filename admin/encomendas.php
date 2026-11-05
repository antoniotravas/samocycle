<?php
require __DIR__ . '/_auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id   = (int)($_POST['id'] ?? 0);
    $novo = $_POST['estado'] ?? '';
    $st = $pdo->prepare("SELECT estado FROM encomendas WHERE id = ?");
    $st->execute([$id]);
    $atual = $st->fetchColumn();

    if ($atual && in_array($novo, ['pendente', 'paga', 'enviada', 'concluida', 'cancelada'], true) && $atual !== $novo) {
        $pdo->beginTransaction();
        // Cancelar devolve o stock; reativar uma cancelada volta a retirá-lo
        if ($novo === 'cancelada' || $atual === 'cancelada') {
            $sinal = $novo === 'cancelada' ? '+' : '-';
            $pdo->prepare("UPDATE produtos p JOIN encomenda_itens i ON i.produto_id = p.id SET p.stock = p.stock $sinal i.quantidade WHERE i.encomenda_id = ?")
                ->execute([$id]);
        }
        $pdo->prepare("UPDATE encomendas SET estado = ?, pago_em = IF(? = 'paga' AND pago_em IS NULL, NOW(), pago_em) WHERE id = ?")
            ->execute([$novo, $novo, $id]);
        $pdo->commit();
        flash('Estado atualizado para “' . estado_label($novo) . '”.');
    }
    redirect('admin/encomendas.php?id=' . $id);
}

admin_header('Encomendas', 'encomendas');

if (isset($_GET['id'])):
    $st = $pdo->prepare("SELECT * FROM encomendas WHERE id = ?");
    $st->execute([(int)$_GET['id']]);
    $o = $st->fetch();
    if (!$o) { echo '<div class="wrap seccao"><p>Encomenda não encontrada.</p></div>'; require __DIR__ . '/../includes/footer.php'; exit; }
    $it = $pdo->prepare("SELECT * FROM encomenda_itens WHERE encomenda_id = ?");
    $it->execute([$o['id']]);
?>
<section class="seccao">
  <div class="wrap duas-col">
    <div>
      <p><a href="<?= url('admin/encomendas.php') ?>">← Todas as encomendas</a></p>
      <h1>Encomenda <?= e($o['referencia']) ?></h1>
      <p><span class="etiqueta etiqueta--<?= e($o['estado']) ?>"><?= e(estado_label($o['estado'])) ?></span>
        criada a <?= date('d/m/Y H:i', strtotime($o['criado_em'])) ?><?= $o['pago_em'] ? ', paga a ' . date('d/m/Y H:i', strtotime($o['pago_em'])) : '' ?></p>
      <table class="tabela" style="margin:var(--s5) 0">
        <thead><tr><th>Artigo</th><th>Qtd.</th><th>Preço</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($it as $i): ?>
          <tr><td><?= e($i['nome']) ?></td><td><?= (int)$i['quantidade'] ?></td><td><?= euro($i['preco']) ?></td><td><?= euro($i['preco'] * $i['quantidade']) ?></td></tr>
        <?php endforeach; ?>
          <tr><td colspan="3">Envio</td><td><?= euro($o['portes']) ?></td></tr>
          <tr><th colspan="3">Total</th><th><?= euro($o['total']) ?></th></tr>
        </tbody>
      </table>
      <h2>Mudar estado</h2>
      <form class="acoes-linha" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
        <?php foreach (['paga' => 'Marcar como paga', 'enviada' => 'Marcar como enviada', 'concluida' => 'Concluir', 'cancelada' => 'Cancelar e repor stock'] as $v => $l): ?>
          <?php if ($v !== $o['estado']): ?>
            <button class="btn btn--pequeno<?= $v === 'cancelada' ? ' btn--contorno' : '' ?>" name="estado" value="<?= $v ?>"><?= $l ?></button>
          <?php endif; ?>
        <?php endforeach; ?>
      </form>
    </div>
    <aside class="resumo">
      <h2>Cliente</h2>
      <p><strong><?= e($o['nome']) ?></strong><br>
        <a href="mailto:<?= e($o['email']) ?>"><?= e($o['email']) ?></a><br>
        <a href="tel:+351<?= e($o['telemovel']) ?>"><?= e($o['telemovel']) ?></a> · <a href="https://wa.me/351<?= e($o['telemovel']) ?>">WhatsApp</a></p>
      <h3>Entrega</h3>
      <p><?= $o['entrega'] === 'loja' ? 'Levanta na oficina' : e($o['morada']) . '<br>' . e($o['codigo_postal']) . ' ' . e($o['localidade']) ?></p>
      <?php if ($o['notas']): ?><h3>Notas</h3><p><?= nl2br(e($o['notas'])) ?></p><?php endif; ?>
      <p class="ajuda">Pedido MB WAY: <?= e($o['mbway_request_id'] ?? '—') ?></p>
    </aside>
  </div>
</section>
<?php
else:
    $filtro = $_GET['estado'] ?? '';
    $sql = "SELECT * FROM encomendas" . ($filtro ? " WHERE estado = ?" : "") . " ORDER BY criado_em DESC LIMIT 200";
    $st = $pdo->prepare($sql);
    $st->execute($filtro ? [$filtro] : []);
    $lista = $st->fetchAll();
?>
<section class="seccao">
  <div class="wrap">
    <h1>Encomendas</h1>
    <nav class="filtros" aria-label="Filtrar por estado">
      <a href="<?= url('admin/encomendas.php') ?>" aria-current="<?= $filtro === '' ? 'true' : 'false' ?>">Todas</a>
      <?php foreach (['pendente', 'paga', 'enviada', 'concluida', 'cancelada'] as $s): ?>
        <a href="<?= url('admin/encomendas.php?estado=' . $s) ?>" aria-current="<?= $filtro === $s ? 'true' : 'false' ?>"><?= e(estado_label($s)) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php if (!$lista): ?>
      <p class="vazio">Sem encomendas neste estado.</p>
    <?php else: ?>
      <div class="tabela-scroll">
        <table class="tabela">
          <thead><tr><th>Referência</th><th>Cliente</th><th>Telemóvel</th><th>Entrega</th><th>Data</th><th>Total</th><th>Estado</th></tr></thead>
          <tbody>
          <?php foreach ($lista as $o): ?>
            <tr>
              <td><a href="<?= url('admin/encomendas.php?id=' . (int)$o['id']) ?>"><?= e($o['referencia']) ?></a></td>
              <td><?= e($o['nome']) ?></td>
              <td><?= e($o['telemovel']) ?></td>
              <td><?= $o['entrega'] === 'loja' ? 'Oficina' : 'CTT' ?></td>
              <td><?= date('d/m/Y H:i', strtotime($o['criado_em'])) ?></td>
              <td><?= euro($o['total']) ?></td>
              <td><span class="etiqueta etiqueta--<?= e($o['estado']) ?>"><?= e(estado_label($o['estado'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
