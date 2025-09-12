document.addEventListener('DOMContentLoaded', () => {
  const form       = document.getElementById('article-filters');
  const output     = document.getElementById('article-results');
  const countBlock = document.getElementById('article-count');
  const resetBtn   = document.getElementById('reset-filters');
  const toggleBtn  = document.querySelector('.filters-toggle');
  const panel      = document.getElementById('filters-panel');

  if (!form || !output || !countBlock) return;

  let timer;

  const applyFilters = () => {
    const params = new URLSearchParams(new FormData(form));

    fetch('/?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(res => res.text())
      .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const newContent = doc.getElementById('article-results');
        const newCount   = doc.getElementById('article-count');

        if (newContent) output.innerHTML     = newContent.innerHTML;
        if (newCount)   countBlock.textContent = newCount.textContent;

        window.history.replaceState(null, '', params.toString() ? '/?' + params.toString() : window.location.pathname);
      })
      .catch(err => console.error('Ошибка фильтрации:', err));
  };

  form.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(applyFilters, 500);
  });
  form.addEventListener('change', applyFilters);

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
      form.querySelectorAll('input[type="text"], input[type="search"]').forEach(inp => inp.value = '');
      form.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);

      fetch('/?', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          const doc = new DOMParser().parseFromString(html, 'text/html');
          const newContent = doc.getElementById('article-results');
          const newCount   = doc.getElementById('article-count');
          if (newContent) output.innerHTML     = newContent.innerHTML;
          if (newCount)   countBlock.textContent = newCount.textContent;
          window.history.replaceState(null, '', window.location.pathname);
        })
        .catch(err => console.error('Ошибка сброса фильтров:', err));
    });
  }

  const setPanelState = (open) => {
    if (!panel || !toggleBtn) return;
    panel.classList.toggle('is-open', open);
    toggleBtn.setAttribute('aria-expanded', open);
    if (open) panel.focus();
  };

  if (toggleBtn && panel) {
    toggleBtn.addEventListener('click', () => {
      setPanelState(!panel.classList.contains('is-open'));
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && panel.classList.contains('is-open')) {
        setPanelState(false);
        toggleBtn.focus();
      }
    });

    document.addEventListener('click', (e) => {
      if (
        panel.classList.contains('is-open') &&
        !panel.contains(e.target) &&
        !toggleBtn.contains(e.target)
      ) {
        setPanelState(false);
      }
    });

    const mq = window.matchMedia('(min-width: 1024px)');
    mq.addEventListener('change', (e) => {
      if (e.matches) {
        panel.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }
});
