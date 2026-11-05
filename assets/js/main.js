// Menu móvel
const btn = document.querySelector('.menu-btn');
const nav = document.getElementById('nav');
if (btn && nav) {
  btn.addEventListener('click', () => {
    const aberto = nav.classList.toggle('aberto');
    btn.setAttribute('aria-expanded', String(aberto));
  });
}

// Carrinho: atualiza automaticamente quando a quantidade muda
document.querySelectorAll('[data-auto-submit]').forEach((el) => {
  el.addEventListener('change', () => el.form && el.form.requestSubmit());
});

// Checkout: mostra a morada só quando a entrega é por envio
const entrega = document.querySelectorAll('input[name="entrega"]');
const morada = document.getElementById('bloco-morada');
if (entrega.length && morada) {
  const sync = () => {
    const envio = document.querySelector('input[name="entrega"]:checked')?.value === 'envio';
    morada.hidden = !envio;
    morada.querySelectorAll('input').forEach((i) => (i.required = envio));
    const t = document.getElementById('totais');
    if (t) {
      document.getElementById('v-portes').textContent = envio ? t.dataset.portes : 'Grátis';
      document.getElementById('v-total').textContent = envio ? t.dataset.envio : t.dataset.loja;
    }
  };
  entrega.forEach((r) => r.addEventListener('change', sync));
  sync();
}

// Página de pagamento: consulta o estado a cada 5 segundos
const espera = document.querySelector('[data-estado-url]');
if (espera) {
  const url = espera.dataset.estadoUrl;
  const t = setInterval(async () => {
    try {
      const r = await fetch(url, { headers: { Accept: 'application/json' } });
      const j = await r.json();
      if (j.estado && j.estado !== 'pendente') {
        clearInterval(t);
        location.reload();
      }
    } catch (_) { /* tenta outra vez */ }
  }, 5000);
}
