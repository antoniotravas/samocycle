<?php
require __DIR__ . '/includes/bootstrap.php';

$servicos = db()->query("SELECT nome FROM servicos ORDER BY ordem")->fetchAll(PDO::FETCH_COLUMN);
$d = ['nome' => '', 'telemovel' => '', 'email' => '', 'bicicleta' => '', 'servico' => '', 'data_preferida' => '', 'mensagem' => ''];
$erros = [];
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($d as $k => $_) $d[$k] = trim((string)($_POST[$k] ?? ''));
    if (!empty($_POST['website'])) exit; // armadilha anti-spam

    if ($d['nome'] === '') $erros['nome'] = 'Indique o seu nome.';
    $tel = telemovel_valido($d['telemovel']);
    if (!$tel) $erros['telemovel'] = 'Indique um telemóvel com 9 dígitos, para confirmarmos a marcação.';
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'O email não parece válido.';
    $data = null;
    if ($d['data_preferida'] !== '') {
        $dt = DateTime::createFromFormat('Y-m-d', $d['data_preferida']);
        if (!$dt || $dt < new DateTime('today')) $erros['data_preferida'] = 'Escolha uma data a partir de hoje.';
        elseif ($dt->format('w') === '0') $erros['data_preferida'] = 'Ao domingo estamos fechados. Escolha outro dia.';
        else $data = $dt->format('Y-m-d');
    }

    if (!$erros) {
        db()->prepare("INSERT INTO pedidos (tipo, nome, telemovel, email, bicicleta, servico, data_preferida, mensagem) VALUES ('reparacao',?,?,?,?,?,?,?)")
            ->execute([$d['nome'], $tel, $d['email'] ?: null, $d['bicicleta'] ?: null, $d['servico'] ?: null, $data, $d['mensagem'] ?: null]);
        $enviado = true;
    }
}

$titulo = 'Marcar reparação';
$pagina = 'marcar';
require __DIR__ . '/includes/header.php';
?>
<header class="cabecalho-pagina">
  <div class="wrap">
    <h1>Marcar reparação</h1>
    <p class="intro">Diga-nos o que se passa com a bicicleta e o dia que lhe dá jeito. Confirmamos por mensagem no próprio dia útil.</p>
  </div>
</header>
<section class="seccao">
  <div class="wrap">
    <?php if ($enviado): ?>
      <div class="vazio" role="status">
        <h2>Pedido recebido</h2>
        <p>Obrigado, <?= e(explode(' ', $d['nome'])[0]) ?>. Vamos ligar ou enviar mensagem para o <?= e($tel) ?> para confirmar a marcação.</p>
        <a class="btn" href="<?= url() ?>">Voltar ao início</a>
      </div>
    <?php else: ?>
      <form class="form form--2" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="so-leitores" aria-hidden="true">
        <?php
        $campo = function (string $k, string $label, string $tipo = 'text', string $extra = '', bool $inteiro = false) use ($d, $erros) {
            $err = isset($erros[$k]);
            echo '<div class="campo' . ($inteiro ? ' inteiro' : '') . ($err ? ' campo--erro' : '') . '">';
            echo '<label for="' . $k . '">' . e($label) . '</label>';
            echo '<input id="' . $k . '" type="' . $tipo . '" name="' . $k . '" value="' . e($d[$k]) . '" ' . $extra . ($err ? ' aria-invalid="true" aria-describedby="e-' . $k . '"' : '') . '>';
            if ($err) echo '<span class="erro-msg" id="e-' . $k . '">' . e($erros[$k]) . '</span>';
            echo '</div>';
        };
        $campo('nome', 'Nome', 'text', 'autocomplete="name" required', true);
        $campo('telemovel', 'Telemóvel', 'tel', 'autocomplete="tel-national" inputmode="numeric" required');
        $campo('email', 'Email (opcional)', 'email', 'autocomplete="email"');
        $campo('bicicleta', 'Bicicleta (opcional)', 'text', 'placeholder="Ex.: BTT Conor 29, estrada Vitoria"');
        $campo('data_preferida', 'Dia preferido (opcional)', 'date', 'min="' . date('Y-m-d') . '"');
        ?>
        <div class="campo inteiro">
          <label for="servico">Serviço</label>
          <select id="servico" name="servico">
            <option value="">Não sei — preciso de diagnóstico</option>
            <?php foreach ($servicos as $s): ?>
              <option<?= $d['servico'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo inteiro">
          <label for="mensagem">O que nota na bicicleta?</label>
          <textarea id="mensagem" name="mensagem" placeholder="Ex.: as mudanças saltam na roda pedaleira pequena, o travão traseiro chia"><?= e($d['mensagem']) ?></textarea>
        </div>
        <div class="inteiro">
          <button class="btn" type="submit">Enviar pedido de marcação</button>
          <p class="ajuda" style="margin-top:var(--s2)">Prefere falar já? <a href="https://wa.me/<?= LOJA_WHATSAPP ?>">WhatsApp</a> ou <a href="tel:+351<?= str_replace(' ', '', LOJA_TELEFONE) ?>"><?= e(LOJA_TELEFONE) ?></a>.</p>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
