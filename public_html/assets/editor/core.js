export const textarea = document.getElementById('content');

export function insertTag(tag, param = null) {
  if (!tag) return;

  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  let selected = textarea.value.slice(start, end);

  let open = `[${tag}]`;
  let close = `[/${tag}]`;

  if (param) {
    if (tag === 'img') {
      open = `[img]`;
      selected = param;
      close = `[/img]`;
    } else {
      open = `[${tag}=${param}]`;
    }
  }

  if (['hr', 'br'].includes(tag)) {
    open = `[${tag}/]`;
    close = '';
  }
  if (tag === '*') {
    open = '[*]';
    close = '';
  }

  const scrollPos = textarea.scrollTop;

  textarea.focus();
  textarea.setSelectionRange(start, end);
  textarea.setRangeText(open + selected + close);

  const cursorStart = start + open.length;
  const cursorEnd = cursorStart + selected.length;
  textarea.setSelectionRange(cursorStart, cursorEnd);
  textarea.scrollTop = scrollPos;
}

export function insertTextAtCursor(text) {
  const start = textarea.selectionStart;
  textarea.setRangeText(text, start, start, 'end');
  textarea.focus();
}

function stripBBCode(text) {
  return text
    .replace(/\[\/?(?:b|i|u|s|url|img|quote|code|color|size|font|heading|spoiler|align|sub|sup|list|olist|\*|table|tr|td|hr|br)(?:=[^\]]+)?\]/gi, '')
    .replace(/\[\/?\w+(?:=[^\]]+)?\]/gi, '')
    .replace(/\[br\/?\]/gi, '\n');
}

document.addEventListener('DOMContentLoaded', () => {
  const clearButton = document.getElementById('clear-content');
  const textarea = document.getElementById('content');

  if (clearButton && textarea) {
    clearButton.addEventListener('click', () => {
      if (confirm('Удалить всё форматирование (BB-коды)?')) {
        textarea.value = stripBBCode(textarea.value);
        textarea.focus();
      }
    });
  }
});
