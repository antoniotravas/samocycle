<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (MODO_PRODUCAO) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => MODO_PRODUCAO]);
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function euro(float|string|null $v): string
{
    return number_format((float)$v, 2, ',', ' ') . ' €';
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Pedido inválido. Volte atrás e recarregue a página.');
    }
}

/* ---------- Mensagens flash ---------- */
function flash(string $msg, string $tipo = 'ok'): void
{
    $_SESSION['flash'][] = ['msg' => $msg, 'tipo' => $tipo];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Carrinho (sessão: produto_id => quantidade) ---------- */
function carrinho(): array
{
    return $_SESSION['carrinho'] ?? [];
}

function carrinho_qtd(): int
{
    return array_sum(carrinho());
}

function carrinho_itens(): array
{
    $c = carrinho();
    if (!$c) return [];
    $ids = array_map('intval', array_keys($c));
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $st  = db()->prepare("SELECT id, nome, marca, preco, stock, imagem, categoria_id FROM produtos WHERE ativo = 1 AND id IN ($in)");
    $st->execute($ids);
    $itens = [];
    foreach ($st as $p) {
        $q = min((int)$c[$p['id']], max(0, (int)$p['stock']));
        if ($q < 1) continue;
        $p['quantidade'] = $q;
        $p['subtotal']   = $q * (float)$p['preco'];
        $itens[] = $p;
    }
    return $itens;
}

function portes(float $subtotal, string $entrega): float
{
    if ($entrega !== 'envio' || $subtotal >= PORTES_GRATIS_ACIMA) return 0.0;
    return PORTES_ENVIO;
}

/* ---------- Validação ---------- */
function telemovel_valido(string $t): ?string
{
    $t = preg_replace('/\D/', '', $t);
    if (str_starts_with($t, '351') && strlen($t) === 12) $t = substr($t, 3);
    return preg_match('/^9[1236]\d{7}$/', $t) ? $t : null;
}

/* ---------- Imagem do produto ---------- */
function produto_img(array $p): string
{
    if (!empty($p['imagem'])) return url('uploads/' . $p['imagem']);
    $icones = [1 => 'bicicleta', 2 => 'peca', 3 => 'roda', 4 => 'acessorio', 5 => 'manutencao'];
    return url('assets/img/cat-' . ($icones[(int)($p['categoria_id'] ?? 0)] ?? 'peca') . '.svg');
}

function estado_label(string $estado): string
{
    return [
        'pendente'  => 'A aguardar pagamento',
        'paga'      => 'Paga',
        'enviada'   => 'Enviada',
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada',
    ][$estado] ?? $estado;
}
