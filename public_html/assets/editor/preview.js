import { textarea } from './core.js';

document.addEventListener('DOMContentLoaded', () => {
  const previewBtn = document.getElementById('preview-toggle');
  const previewBox = document.getElementById('bb-preview');

  if (!previewBtn || !previewBox || !textarea) return;

  let previewEnabled = false;
  let debounce;
  let lastValue = textarea.value;

  const LS_KEY = 'article_preview_enabled';

  async function loadPreview() {
    if (!textarea.value.trim()) {
      previewBox.innerHTML = '';
      return;
    }

    previewBox.innerHTML = '<div class="preview-loading">⏳ Загрузка предпросмотра...</div>';

    try {
      const csrfInput = document.querySelector('input[name="csrf_token"]');
      const csrfToken = csrfInput ? csrfInput.value : '';

      const response = await fetch('/api/preview', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ content: textarea.value })
      });

      if (!response.ok) {
        throw new Error(`Ошибка загрузки предпросмотра (${response.status})`);
      }

      const result = await response.json();

      if (!result.success || !result.data) {
        throw new Error('Некорректный ответ от сервера');
      }

      const html =
        result.data.html ??
        (result.data.attributes && result.data.attributes.html);

      if (!html) {
        throw new Error('Некорректный ответ от сервера');
      }

      previewBox.innerHTML = html;
    } catch (err) {
      previewBox.innerHTML = `<div style="color:red;">⚠ ${err.message}</div>`;
    }
  }

  function enablePreview() {
    previewEnabled = true;
    previewBox.hidden = false;
    previewBtn.textContent = '⛔ Выключить предпросмотр';
    textarea.addEventListener('input', handleInput);

    try {
      localStorage.setItem(LS_KEY, '1');
    } catch {}

    loadPreview();
  }

  function disablePreview() {
    previewEnabled = false;
    previewBox.hidden = true;
    previewBtn.textContent = '👁️ Включить предпросмотр';
    textarea.removeEventListener('input', handleInput);

    try {
      localStorage.setItem(LS_KEY, '0');
    } catch {}
  }

  function handleInput() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      if (textarea.value !== lastValue) {
        lastValue = textarea.value;
        loadPreview();
      }
    }, 600);
  }

  previewBtn.addEventListener('click', () => {
    previewEnabled ? disablePreview() : enablePreview();
  });

  try {
    const saved = localStorage.getItem(LS_KEY);
    if (saved === '1') {
      enablePreview();
    } else {
      disablePreview();
    }
  } catch {
    disablePreview();
  }
});
