<?php
require __DIR__ . '/_auth.php';

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($acao === 'apagar') {
        $pdo->prepare("DELETE FROM servicos WHERE id = ?")->execute([$id]);
        flash('Serviço removido da tabela.');
    } else {
        $v = [
            trim($_POST['grupo'] ?? ''), trim($_POST['nome'] ?? ''), trim($_POST['descricao'] ?? '') ?: null,
            (float)str_replace(',', '.', $_POST['preco'] ?? '0'), !empty($_POST['preco_desde']) ? 1 : 0, (int)($_POST['ordem'] ?? 0),
        ];
        if ($v[0] === '' || $v[1] === '') {
            flash('Grupo e nome são obrigatórios.', 'erro');
        } elseif ($id) {
            $pdo->prepare("UPDATE servicos SET grupo=?, nome=?, descricao=?, preco=?, preco_desde=?, ordem=? WHERE id=?")->execute([...$v, $id]);
            flash('Serviço atualizado.');
        } else {
            $pdo->prepare("INSERT INTO servicos (grupo, nome, descricao, preco, preco_desde, ordem) VALUES (?,?,?,?,?,?)")->execute($v);
            flash('Serviço adicionado.');
        }
    }
    redirect('admin/servicos.php');
}

$lista = $pdo->query("SELECT * FROM servicos ORDER BY ordem")->fetchAll();
$lista[] = ['id' => 0, 'grupo' => '', 'nome' => '', 'descricao' => '', 'preco' => '', 'preco_desde' => 0, 'ordem' => count($lista) + 1];

admin_header('Preços da oficina', 'servicos');
?>
<section class="seccao">
  <div class="wrap">
    <h1>Preços da oficina</h1>
    <p class="intro">Cada linha é guardada separadamente. A última linha, em branco, serve para adicionar um serviço novo. Preço 0 aparece como “Grátis”.</p>
    <div class="tabela-scroll">
      <table class="tabela">
        <thead><tr><th>Ordem</th><th>Grupo</th><th>Serviço</th><th>Descrição</th><th>Preço €</th><th>“desde”</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $s): $f = 'f' . (int)$s['id']; ?>
          <tr>
            <td><input form="<?= $f ?>" type="number" name="ordem" value="<?= (int)$s['ordem'] ?>" style="width:4.5rem" aria-label="Ordem"></td>
            <td><input form="<?= $f ?>" type="text" name="grupo" value="<?= e($s['grupo']) ?>" aria-label="Grupo" placeholder="Ex.: Rodas"></td>
            <td><input form="<?= $f ?>" type="text" name="nome" value="<?= e($s['nome']) ?>" aria-label="Serviço"></td>
            <td><input form="<?= $f ?>" type="text" name="descricao" value="<?= e($s['descricao']) ?>" aria-label="Descrição"></td>
            <td><input form="<?= $f ?>" type="text" inputmode="decimal" name="preco" value="<?= e((string)$s['preco']) ?>" style="width:6rem" aria-label="Preço"></td>
            <td><input form="<?= $f ?>" type="checkbox" name="preco_desde" value="1"<?= $s['preco_desde'] ? ' checked' : '' ?> aria-label="Preço a partir de"></td>
            <td>
              <div class="acoes-linha">
                <form id="<?= $f ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <button class="btn btn--pequeno"><?= $s['id'] ? 'Guardar' : 'Adicionar' ?></button></form>
                <?php if ($s['id']): ?>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="acao" value="apagar">
                    <button class="link-btn">Remover</button></form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
