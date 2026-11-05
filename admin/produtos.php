<?php
require __DIR__ . '/_auth.php';

$pdo = db();
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem")->fetchAll();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);

    if (($_POST['acao'] ?? '') === 'ativo') {
        $pdo->prepare("UPDATE produtos SET ativo = 1 - ativo WHERE id = ?")->execute([$id]);
        redirect('admin/produtos.php');
    }

    $p = [
        'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
        'nome'         => trim($_POST['nome'] ?? ''),
        'marca'        => trim($_POST['marca'] ?? '') ?: null,
        'descricao'    => trim($_POST['descricao'] ?? '') ?: null,
        'preco'        => (float)str_replace(',', '.', $_POST['preco'] ?? '0'),
        'stock'        => max(0, (int)($_POST['stock'] ?? 0)),
        'destaque'     => !empty($_POST['destaque']) ? 1 : 0,
        'ativo'        => !empty($_POST['ativo']) ? 1 : 0,
    ];
    if ($p['nome'] === '') $erros['nome'] = 'Indique o nome.';
    if ($p['preco'] <= 0) $erros['preco'] = 'Indique um preço maior que zero.';
    if (!in_array($p['categoria_id'], array_column($categorias, 'id'))) $erros['categoria_id'] = 'Escolha a categoria.';

    // Imagem (opcional): JPG, PNG ou WebP até 4 MB
    $imagem = null;
    if (!empty($_FILES['imagem']['name'])) {
        $f = $_FILES['imagem'];
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
        if (!isset($tipos[$mime])) $erros['imagem'] = 'A imagem tem de ser JPG, PNG ou WebP.';
        elseif ($f['size'] > 4 * 1024 * 1024) $erros['imagem'] = 'A imagem tem mais de 4 MB.';
        else {
            $imagem = bin2hex(random_bytes(8)) . '.' . $tipos[$mime];
            if (!is_dir(__DIR__ . '/../uploads')) mkdir(__DIR__ . '/../uploads', 0755, true);
            move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $imagem);
        }
    }

    if (!$erros) {
        if ($id) {
            $sets = 'categoria_id=?, nome=?, marca=?, descricao=?, preco=?, stock=?, destaque=?, ativo=?' . ($imagem ? ', imagem=?' : '');
            $vals = array_values($p);
            if ($imagem) $vals[] = $imagem;
            $vals[] = $id;
            $pdo->prepare("UPDATE produtos SET $sets WHERE id = ?")->execute($vals);
        } else {
            $pdo->prepare("INSERT INTO produtos (categoria_id, nome, marca, descricao, preco, stock, destaque, ativo, imagem) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([...array_values($p), $imagem]);
        }
        flash('Produto guardado.');
        redirect('admin/produtos.php');
    }
    $p['id'] = $id;
    $editar = $p;
}

if (!isset($editar) && (isset($_GET['editar']) || isset($_GET['novo']))) {
    $editar = ['id' => 0, 'categoria_id' => 0, 'nome' => '', 'marca' => '', 'descricao' => '', 'preco' => '', 'stock' => 1, 'destaque' => 0, 'ativo' => 1, 'imagem' => null];
    if (!empty($_GET['editar'])) {
        $st = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $st->execute([(int)$_GET['editar']]);
        $editar = $st->fetch() ?: $editar;
    }
}

admin_header('Produtos', 'produtos');

if (isset($editar)):
    $err = fn($k) => isset($erros[$k]) ? '<span class="erro-msg">' . e($erros[$k]) . '</span>' : '';
?>
<section class="seccao">
  <div class="wrap">
    <p><a href="<?= url('admin/produtos.php') ?>">← Produtos</a></p>
    <h1><?= $editar['id'] ? 'Editar produto' : 'Novo produto' ?></h1>
    <form class="form form--2" method="post" enctype="multipart/form-data" style="max-width:48rem">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$editar['id'] ?>">
      <div class="campo inteiro"><label for="nome">Nome</label><input id="nome" type="text" name="nome" value="<?= e($editar['nome']) ?>" required><?= $err('nome') ?></div>
      <div class="campo"><label for="marca">Marca</label><input id="marca" type="text" name="marca" value="<?= e($editar['marca']) ?>"></div>
      <div class="campo"><label for="cat">Categoria</label>
        <select id="cat" name="categoria_id">
          <option value="">Escolha…</option>
          <?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>"<?= (int)$editar['categoria_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
        </select><?= $err('categoria_id') ?></div>
      <div class="campo"><label for="preco">Preço (€, com IVA)</label><input id="preco" type="text" inputmode="decimal" name="preco" value="<?= e((string)$editar['preco']) ?>" required><?= $err('preco') ?></div>
      <div class="campo"><label for="stock">Stock</label><input id="stock" type="number" min="0" name="stock" value="<?= (int)$editar['stock'] ?>"></div>
      <div class="campo inteiro"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" rows="5"><?= e($editar['descricao']) ?></textarea></div>
      <div class="campo inteiro"><label for="imagem">Fotografia</label>
        <?php if (!empty($editar['imagem'])): ?><img src="<?= url('uploads/' . $editar['imagem']) ?>" alt="" style="width:10rem;border-radius:var(--raio)"><span class="ajuda">Escolha outra para substituir.</span><?php endif; ?>
        <input id="imagem" type="file" name="imagem" accept="image/jpeg,image/png,image/webp"><span class="ajuda">JPG, PNG ou WebP até 4 MB. De preferência na horizontal (4:3).</span><?= $err('imagem') ?></div>
      <label class="opcao"><input type="checkbox" name="destaque" value="1"<?= $editar['destaque'] ? ' checked' : '' ?>><span>Mostrar na página inicial</span></label>
      <label class="opcao"><input type="checkbox" name="ativo" value="1"<?= $editar['ativo'] ? ' checked' : '' ?>><span>Visível na loja</span></label>
      <div class="inteiro"><button class="btn" type="submit">Guardar produto</button></div>
    </form>
  </div>
</section>
<?php
else:
    $soEsgotados = isset($_GET['stock']);
    $lista = $pdo->query("SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON c.id = p.categoria_id"
        . ($soEsgotados ? " WHERE p.stock < 1" : "") . " ORDER BY c.ordem, p.nome")->fetchAll();
?>
<section class="seccao">
  <div class="wrap">
    <div class="seccao__topo">
      <h1>Produtos</h1>
      <a class="btn" href="<?= url('admin/produtos.php?novo=1') ?>">Adicionar produto</a>
    </div>
    <div class="tabela-scroll">
      <table class="tabela">
        <thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Stock</th><th>Loja</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $p): ?>
          <tr>
            <td><strong><?= e($p['nome']) ?></strong><?= $p['marca'] ? '<br><span class="ajuda">' . e($p['marca']) . '</span>' : '' ?><?= $p['destaque'] ? ' <span class="etiqueta">Destaque</span>' : '' ?></td>
            <td><?= e($p['categoria']) ?></td>
            <td><?= euro($p['preco']) ?></td>
            <td><?= (int)$p['stock'] < 1 ? '<span class="esgotado">0</span>' : (int)$p['stock'] ?></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="ativo"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="link-btn"><?= $p['ativo'] ? 'Visível — ocultar' : 'Oculto — mostrar' ?></button></form>
            </td>
            <td><a href="<?= url('admin/produtos.php?editar=' . (int)$p['id']) ?>">Editar</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
