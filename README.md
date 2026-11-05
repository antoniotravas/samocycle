# Samocycle — Bike for all

Site da oficina e loja Samocycle (PHP 8 + MySQL, HTML/CSS/JS sem frameworks).

## Correr localmente (XAMPP)

1. Arrancar Apache e MySQL no painel do XAMPP.
2. Importar `sql/schema.sql` (phpMyAdmin → Importar). Cria a base `samocycle` com dados de exemplo.
3. A pasta do projeto está ligada a `C:\xampp\htdocs\samocycle` (junction). Abrir http://localhost/samocycle
4. Administração: http://localhost/samocycle/admin — utilizador `admin`, palavra-passe `samocycle2026` (mudar em Admin → Conta).

## Configuração — `config/config.php`

- Dados da loja: morada, email e horário estão por preencher/confirmar.
- `MBWAY_MODO`:
  - `simulacao` — testes; a página de pagamento tem um botão “Simular pagamento aceite”.
  - `manual` — o cliente envia MB WAY para o número da loja e o admin marca a encomenda como paga.
  - `ifthenpay` — pagamento automático. Precisa de contrato com a ifthenpay, `IFTHENPAY_MBWAY_KEY`,
    `IFTHENPAY_ANTIPHISHING` e o callback configurado para `/mbway-callback.php` (ver o ficheiro).
- `BASE_URL` — mudar se o site ficar na raiz do domínio (`''`).

## Estrutura

- `index.php`, `servicos.php`, `loja.php`, `produto.php`, `marcar.php`, `contactos.php` — páginas públicas
- `carrinho.php` → `checkout.php` → `pagamento.php` — compra com MB WAY
- `admin/` — encomendas, marcações e mensagens, produtos (com fotografias), preços da oficina, conta
- `includes/` — arranque, cabeçalho/rodapé, MB WAY
- `uploads/` — fotografias dos produtos (scripts bloqueados)
