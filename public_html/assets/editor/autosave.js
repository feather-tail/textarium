import { textarea } from './core.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.article-form');

  if (!textarea || !form || !form.dataset.draftId) return;

  const draftId = form.dataset.draftId;
  console.log('[autosave init]', { draftId, textarea });

  let lastSaved = textarea.value;

  function autosave() {
    if (textarea.value === lastSaved) return;

    fetch('/api/autosave', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': document.querySelector('#csrf-token')?.value || ''
      },
      body: JSON.stringify({
        id: draftId,
        content: textarea.value
      })
    }).then(r => r.text()).then(res => {
      console.log('[autosave]', res);
      lastSaved = textarea.value;
    }).catch(err => {
      console.warn('[autosave failed]', err);
    });
  }

  setInterval(autosave, 15000);
});
