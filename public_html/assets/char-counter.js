document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-maxlength]').forEach((input) => {
    const max = parseInt(input.dataset.maxlength, 10);
    const counter = document.createElement('div');
    counter.className = 'char-remaining';
    counter.style.fontSize = '0.85em';
    counter.style.color = '#666';
    counter.style.textAlign = 'right';
    counter.style.marginTop = '2px';

    input.parentNode.insertBefore(counter, input.nextSibling);

    const updateCounter = () => {
      const length = input.value.length;
      const left = max - length;
      counter.textContent = `Осталось символов: ${left}`;
      counter.style.color = left < 0 ? 'red' : '#666';
    };

    input.addEventListener('input', updateCounter);
    updateCounter();
  });
});
