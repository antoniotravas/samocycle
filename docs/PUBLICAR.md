# Pôr o site Samocycle online (alojamento com cPanel)

Funciona em PTisp, Amen, Hostinger, PTServidor e na maioria dos alojamentos partilhados com cPanel.
Requisitos: PHP 8.1 ou superior, MySQL/MariaDB, extensões `pdo_mysql`, `curl` e `fileinfo`.

## 1. Domínio e alojamento
- Contratar em nome da Samocycle (NIF da empresa).
- Se o domínio for registado noutro fornecedor, apontar os DNS (nameservers) para o alojamento.
- No cPanel → **Seleccionar versão PHP**: escolher 8.2 ou 8.3 e confirmar as extensões acima.

## 2. Base de dados
No cPanel → **Bases de dados MySQL** (ou "Assistente de base de dados MySQL"):
1. Criar a base de dados, por exemplo `samocycle` (o cPanel junta um prefixo: `conta_samocycle`).
2. Criar um utilizador, por exemplo `samocycle`, com uma palavra-passe forte (usar o gerador do cPanel e guardar num gestor de palavras-passe).
3. Adicionar o utilizador à base de dados com **TODOS OS PRIVILÉGIOS**.
4. Abrir o **phpMyAdmin** → selecionar a base de dados à esquerda → **Importar** → escolher `sql/producao.sql` → Executar.
   (Não usar `schema.sql`: tem `CREATE DATABASE`, que o alojamento partilhado não deixa usar.)

## 3. Ficheiros
1. cPanel → **Gestor de ficheiros** → `public_html`.
2. **Carregar** `dist/samocycle-site.zip` → botão direito → **Extrair**. Apagar depois o zip.
3. Ativar "Mostrar ficheiros ocultos" e confirmar que existe `public_html/.htaccess`.
4. Em `public_html/config/`, copiar `config.local.example.php` para **`config.local.php`** e editar:
   `DB_NAME`, `DB_USER`, `DB_PASS` (do passo 2), `LOJA_EMAIL`, `LOJA_MORADA`, `MBWAY_MODO` = `'manual'`.
5. Confirmar que a pasta `uploads` tem permissões 755.

## 4. HTTPS e email
- cPanel → **SSL/TLS Status** (ou "Let's Encrypt") → emitir o certificado para `samocycle.pt` e `www.samocycle.pt`.
- O `.htaccess` já redireciona tudo para https.
- cPanel → **Contas de email** → criar `geral@samocycle.pt`.

## 5. Verificar
- Abrir https://samocycle.pt e percorrer as páginas.
- https://samocycle.pt/config/config.php deve dar **403 Proibido**.
- Entrar em https://samocycle.pt/admin com `admin` / `samocycle2026` e **mudar logo a palavra-passe** em Admin → Conta.
- Substituir os produtos e preços de exemplo pelos reais.
- Fazer uma encomenda de teste, marcá-la como paga e depois cancelá-la (o stock volta).

## 6. Quando a ifthenpay aprovar a conta
1. No backoffice da ifthenpay, copiar a **chave MB WAY** e definir uma **chave anti-phishing** longa.
2. Configurar o callback:
   `https://samocycle.pt/mbway-callback.php?key=[ANTI_PHISHING_KEY]&orderId=[ORDER_ID]&amount=[AMOUNT]&requestId=[REQUEST_ID]`
3. Em `config.local.php`: `MBWAY_MODO` = `'ifthenpay'`, `IFTHENPAY_MBWAY_KEY` e `IFTHENPAY_ANTIPHISHING`.
4. Fazer uma compra real de valor baixo para confirmar.

## Cópias de segurança
Ativar as cópias automáticas do alojamento (JetBackup ou equivalente) ou exportar a base de dados no phpMyAdmin todos os meses.
A pasta `uploads/` (fotografias dos produtos) também deve entrar nas cópias.
