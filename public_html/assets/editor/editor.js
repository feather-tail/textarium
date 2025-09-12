import './core.js';
import './modal.js';
import './preview.js';
import './autosave.js';
import './dnd-upload.js';

import { insertTag, textarea } from './core.js';

document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.bb-btn');

  const modal = document.getElementById('bbcode-modal');
  const modalLabel = document.getElementById('bbcode-modal-label');
  const modalInput = document.getElementById('bbcode-modal-input');
  const modalSelect = document.getElementById('bbcode-modal-select');
  const modalColorPalette = document.getElementById('bbcode-color-palette');
  const modalAlignButtons = document.getElementById('bbcode-align-buttons');
  const modalOk = document.getElementById('bbcode-modal-ok');
  const modalCancel = document.getElementById('bbcode-modal-cancel');

  const fontOptions = [
    'Arial', 'Georgia', 'Courier New', 'Tahoma',
    'Verdana', 'Times New Roman', 'Comic Sans MS', 'Impact'
  ];

  const sizeOptions = [
    '10', '12', '14', '16', '18', '20', '24', '28', '32'
  ];

  const headingOptions = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

  function showModal(tag) {
    modal.classList.remove('hidden');
    modalInput.classList.add('hidden');
    modalSelect.classList.add('hidden');
    modalColorPalette.classList.add('hidden');
    modalAlignButtons.classList.add('hidden');
    modalOk.classList.remove('hidden');
    modalOk.disabled = false;
    let value = '';

    modalOk.onclick = null;

    if (tag === 'url') {
      modalLabel.textContent = 'Введите ссылку';
      modalInput.type = 'text';
      modalInput.value = '';
      modalInput.classList.remove('hidden');
      modalInput.placeholder = 'https://example.com';
      modalInput.focus();

      modalOk.onclick = () => {
        value = modalInput.value.trim();
        if (value.length < 3) return;
        insertTag('url', value);
        closeModal();
      };
    } else if (tag === 'img') {
      modalLabel.textContent = 'URL изображения';
      modalInput.type = 'text';
      modalInput.value = '';
      modalInput.classList.remove('hidden');
      modalInput.placeholder = 'https://site.com/image.jpg';
      modalInput.focus();

      modalOk.onclick = () => {
        value = modalInput.value.trim();
        if (!/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp)$/i.test(value)) {
          alert('Некорректный URL изображения');
          return;
        }
        insertTag('img', value);
        closeModal();
      };
    } else if (tag === 'spoiler') {
      modalLabel.textContent = 'Заголовок спойлера (необязательно)';
      modalInput.type = 'text';
      modalInput.value = '';
      modalInput.classList.remove('hidden');
      modalInput.placeholder = 'Заголовок (или оставьте пустым)';
      modalInput.focus();

      modalOk.onclick = () => {
        value = modalInput.value.trim();
        insertTag('spoiler', value ? value : null);
        closeModal();
      };
    } else if (tag === 'quote') {
      modalLabel.textContent = 'Автор цитаты (необязательно)';
      modalInput.type = 'text';
      modalInput.value = '';
      modalInput.classList.remove('hidden');
      modalInput.placeholder = 'Имя автора или оставить пустым';
      modalInput.focus();

      modalOk.onclick = () => {
        value = modalInput.value.trim();
        insertTag('quote', value ? value : null);
        closeModal();
      };
    } else if (tag === 'color') {
      modalLabel.textContent = 'Выберите цвет';
      modalColorPalette.innerHTML = '';
      modalColorPalette.classList.remove('hidden');

      const colors = [
        '#000000', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF',
        '#FFFFFF', '#808080', '#800000', '#008000', '#000080', '#808000', '#800080', '#008080'
      ];
      colors.forEach(col => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'color-btn';
        btn.style.background = col;
        btn.style.width = '24px';
        btn.style.height = '24px';
        btn.title = col;
        btn.onclick = () => {
          insertTag('color', col);
          closeModal();
        };
        modalColorPalette.appendChild(btn);
      });
      modalOk.classList.add('hidden');
    } else if (tag === 'font') {
      modalLabel.textContent = 'Выберите шрифт';
      modalSelect.innerHTML = '';
      fontOptions.forEach(opt => {
        const o = document.createElement('option');
        o.value = opt;
        o.textContent = opt;
        modalSelect.appendChild(o);
      });
      modalSelect.classList.remove('hidden');
      modalOk.onclick = () => {
        value = modalSelect.value;
        insertTag('font', value);
        closeModal();
      };
    } else if (tag === 'size') {
      modalLabel.textContent = 'Выберите размер';
      modalSelect.innerHTML = '';
      sizeOptions.forEach(opt => {
        const o = document.createElement('option');
        o.value = opt;
        o.textContent = opt + ' px';
        modalSelect.appendChild(o);
      });
      modalSelect.classList.remove('hidden');
      modalOk.onclick = () => {
        value = modalSelect.value;
        insertTag('size', value);
        closeModal();
      };
    } else if (tag === 'align') {
      modalLabel.textContent = 'Выберите выравнивание';
      modalAlignButtons.classList.remove('hidden');
      modalAlignButtons.querySelectorAll('.bb-align-option').forEach(btn => {
        btn.onclick = () => {
          const align = btn.dataset.align;
          insertTag('align', align);
          closeModal();
        };
      });
      modalOk.classList.add('hidden');
    } else if (tag === 'heading') {
      modalLabel.textContent = 'Выберите тип заголовка';
      modalSelect.innerHTML = '';
      headingOptions.forEach(opt => {
        const o = document.createElement('option');
        o.value = opt;
        o.textContent = opt.toUpperCase();
        modalSelect.appendChild(o);
      });
      modalSelect.classList.remove('hidden');
      modalOk.onclick = () => {
        value = modalSelect.value;
        insertTag(value);
        closeModal();
      };
    }
  }

  function closeModal() {
    modal.classList.add('hidden');
    modalInput.value = '';
    modalInput.classList.add('hidden');
    modalSelect.classList.add('hidden');
    modalColorPalette.classList.add('hidden');
    modalAlignButtons.classList.add('hidden');
    modalOk.classList.add('hidden');
  }

  modalCancel.onclick = () => closeModal();

  const tableModal = document.getElementById('bbcode-table-modal');
  const tableLabel = document.getElementById('table-size-label');
  const tableGrid = document.getElementById('table-grid-preview');

  const tableModalClose = document.getElementById('table-modal-close');
  if (tableModalClose) {
    tableModalClose.onclick = () => closeTableModal();
  }

  const maxRows = 10;
  const maxCols = 10;

  function renderTableGrid() {
    tableGrid.innerHTML = '';
    for (let row = 1; row <= maxRows; row++) {
      const tr = document.createElement('div');
      tr.className = 'table-grid-row';
      for (let col = 1; col <= maxCols; col++) {
        const cell = document.createElement('div');
        cell.className = 'table-grid-cell';
        cell.dataset.row = row;
        cell.dataset.col = col;
        cell.addEventListener('mouseenter', () => {
          highlightGrid(row, col);
          tableLabel.textContent = `${row} x ${col}`;
        });
        cell.addEventListener('mouseleave', () => {
          tableLabel.textContent = `0 x 0`;
          highlightGrid(0, 0);
        });
        cell.addEventListener('click', () => {
          insertTableBBCode(row, col);
          closeTableModal();
        });
        tr.appendChild(cell);
      }
      tableGrid.appendChild(tr);
    }
  }

  function highlightGrid(rows, cols) {
    const cells = tableGrid.querySelectorAll('.table-grid-cell');
    cells.forEach(cell => {
      const r = parseInt(cell.dataset.row, 10);
      const c = parseInt(cell.dataset.col, 10);
      if (r <= rows && c <= cols) {
        cell.classList.add('active');
      } else {
        cell.classList.remove('active');
      }
    });
  }

  function openTableModal() {
    renderTableGrid();
    tableModal.classList.remove('hidden');
    tableLabel.textContent = '0 x 0';
  }

  function closeTableModal() {
    tableModal.classList.add('hidden');
  }

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tag = btn.dataset.tag;
      if (['url', 'img', 'spoiler', 'color', 'font', 'size', 'align', 'heading', 'quote'].includes(tag)) {
        showModal(tag);
        return;
      }
      if (tag === 'table') {
        openTableModal();
        return;
      }
      if (tag === 'list') {
        const listText = `[list]\n[*] Первый элемент\n[*] Второй элемент\n[/list]`;
        textarea.setRangeText(listText, textarea.selectionStart, textarea.selectionEnd, 'end');
        textarea.focus();
        return;
      }
      if (tag === 'list1') {
        const listText = `[olist]\n[*] Первый пункт\n[*] Второй пункт\n[/olist]`;
        textarea.setRangeText(listText, textarea.selectionStart, textarea.selectionEnd, 'end');
        textarea.focus();
        return;
      }
      insertTag(tag);
    });
  });

  function insertTableBBCode(rows, cols) {
    let bbcode = '[table]\n';
    for (let r = 0; r < rows; r++) {
      bbcode += '  [tr]';
      for (let c = 0; c < cols; c++) {
        bbcode += '[td][/td]';
      }
      bbcode += '[/tr]\n';
    }
    bbcode += '[/table]\n';
    const pos = textarea.selectionStart;
    textarea.setRangeText(bbcode, pos, pos, 'end');
    textarea.focus();
  }

  modal.addEventListener('mousedown', function(e) {
    if (e.target === modal) closeModal();
  });

  tableModal.addEventListener('mousedown', function(e) {
    if (e.target === tableModal) closeTableModal();
  });
});
