<?php
require __DIR__ . '/_auth.php';

$pdo = db();
$k = [
    'pendentes' => $pdo->query("SELECT COUNT(*) FROM encomendas WHERE estado = 'pendente'")->fetchColumn(),
    'por_enviar' => $pdo->query("SELECT COUNT(*) FROM encomendas WHERE estado = 'paga'")->fetchColumn(),
    'pedidos'   => $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'novo'")->fetchColumn(),
    'mes'       => $pdo->query("SELECT COALESCE(SUM(total),0) FROM encomendas WHERE estado IN ('paga','enviada','concluida') AND pago_em >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn(),
    'sem_stock' => $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND stock < 1")->fetchColumn(),
];
$ultimas = $pdo->query("SELECT * FROM encomendas ORDER BY criado_em DESC LIMIT 6")->fetchAll();

admin_header('Painel', 'painel');
?>
<section class="seccao">
  <div class="wrap">
    <h1>Painel</h1>
    <div class="kpis">
      <a class="kpi" href="<?= url('admin/encomendas.php?estado=paga') ?>" style="text-decoration:none;color:inherit"><strong><?= (int)$k['por_enviar'] ?></strong><span>pagas, por preparar</span></a>
      <a class="kpi" href="<?= url('admin/encomendas.php?estado=pendente') ?>" style="text-decoration:none;color:inherit"><strong><?= (int)$k['pendentes'] ?></strong><span>a aguardar pagamento</span></a>
      <a class="kpi" href="<?= url('admin/pedidos.php') ?>" style="text-decoration:none;color:inherit"><strong><?= (int)$k['pedidos'] ?></strong><span>marcações e mensagens novas</span></a>
      <div class="kpi"><strong><?= euro($k['mes']) ?></strong><span>vendas pagas este mês</span></div>
      <a class="kpi" href="<?= url('admin/produtos.php?stock=0') ?>" style="text-decoration:none;color:inherit"><strong><?= (int)$k['sem_stock'] ?></strong><span>produtos esgotados</span></a>
    </div>

    <h2>Últimas encomendas</h2>
    <?php if (!$ultimas): ?>
      <p class="vazio">Ainda não há encomendas.</p>
    <?php else: ?>
      <div class="tabela-scroll">
        <table class="tabela">
          <thead><tr><th>Referência</th><th>Cliente</th><th>Data</th><th>Total</th><th>Estado</th></tr></thead>
          <tbody>
          <?php foreach ($ultimas as $o): ?>
            <tr>
              <td><a href="<?= url('admin/encomendas.php?id=' . (int)$o['id']) ?>"><?= e($o['referencia']) ?></a></td>
              <td><?= e($o['nome']) ?></td>
              <td><?= date('d/m H:i', strtotime($o['criado_em'])) ?></td>
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
<?php require __DIR__ . '/../includes/footer.php'; ?>
