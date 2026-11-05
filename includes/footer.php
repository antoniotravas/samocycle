</main>
<footer class="rodape">
  <div class="wrap rodape__in">
    <div>
      <img src="<?= url('assets/img/logo.svg') ?>" alt="" width="56" height="56">
      <p class="rodape__slogan">Bike for all.</p>
      <p>Oficina e loja de bicicletas.<br>Revendedor Vitoria Bikes, Deed Bikes, Conor-WRC e Rothar Wheels.</p>
    </div>
    <div>
      <h2>Contactos</h2>
      <p><a href="tel:+351<?= str_replace(' ', '', LOJA_TELEFONE) ?>"><?= e(LOJA_TELEFONE) ?></a> (chamada para a rede móvel nacional)</p>
      <p><a href="https://wa.me/<?= LOJA_WHATSAPP ?>">WhatsApp</a></p>
      <p><a href="mailto:<?= e(LOJA_EMAIL) ?>"><?= e(LOJA_EMAIL) ?></a></p>
      <p><?= e(LOJA_MORADA) ?></p>
    </div>
    <div>
      <h2>Horário</h2>
      <dl class="horario">
        <?php foreach (LOJA_HORARIO as $d => $h): ?>
          <dt><?= e($d) ?></dt><dd><?= e($h) ?></dd>
        <?php endforeach; ?>
      </dl>
    </div>
    <div>
      <h2>Siga-nos</h2>
      <p><a href="<?= FACEBOOK_URL ?>" rel="noopener">Facebook</a></p>
      <p><a href="<?= INSTAGRAM_URL ?>" rel="noopener">Instagram</a></p>
    </div>
  </div>
  <div class="wrap rodape__fim">
    <p>© <?= date('Y') ?> Samocycle. Pagamentos por MB WAY.</p>
    <p><a href="https://www.livroreclamacoes.pt" rel="noopener">Livro de Reclamações</a></p>
  </div>
</footer>
<script src="<?= url('assets/js/main.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
</body>
</html>
