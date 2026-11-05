<?php
require __DIR__ . '/../includes/bootstrap.php';

if (empty($_SESSION['admin_id'])) {
    redirect('admin/login.php');
}

function admin_header(string $titulo, string $ativo): void
{
    $titulo = 'Admin · ' . $titulo;
    $pagina = '';
    require __DIR__ . '/../includes/header.php';
    $links = [
        'painel'     => ['admin/', 'Painel'],
        'encomendas' => ['admin/encomendas.php', 'Encomendas'],
        'pedidos'    => ['admin/pedidos.php', 'Marcações e mensagens'],
        'produtos'   => ['admin/produtos.php', 'Produtos'],
        'servicos'   => ['admin/servicos.php', 'Preços da oficina'],
        'conta'      => ['admin/conta.php', 'Conta'],
    ];
    echo '<nav class="admin-nav" aria-label="Administração"><div class="wrap">';
    foreach ($links as $k => [$h, $l]) {
        echo '<a href="' . url($h) . '"' . ($k === $ativo ? ' aria-current="page"' : '') . '>' . e($l) . '</a>';
    }
    echo '<form method="post" action="' . url('admin/logout.php') . '" style="margin-left:auto">' . csrf_field()
       . '<button class="link-btn" style="color:#E4E8E2;padding:var(--s2) var(--s3)">Sair</button></form>';
    echo '</div></nav>';
}
