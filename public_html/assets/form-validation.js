document.addEventListener('DOMContentLoaded', () => {
  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.append(toastContainer);
  }

  for (const form of document.querySelectorAll('form.article-form')) {
    form.addEventListener('submit', e => {
      e.preventDefault();

      form.querySelectorAll('.field--error').forEach(el => el.classList.remove('field--error'));
      toastContainer.innerHTML = '';

      const errors = [];

      const title = form.querySelector('[name="title"]');
      if (!title || !title.value.trim()) {
        errors.push({ field: title, message: 'Заголовок не заполнен' });
      }

      const cats = form.querySelectorAll('[name="categories[]"]:checked');
      if (cats.length === 0) {
        const fs = form.querySelector('fieldset.form-group:nth-of-type(1)');
        errors.push({ field: fs, message: 'Нужно выбрать хотя бы одну категорию' });
      }

      const tags = form.querySelectorAll('[name="tags[]"]:checked');
      if (tags.length === 0) {
        const fs = form.querySelector('fieldset.form-group:nth-of-type(2)');
        errors.push({ field: fs, message: 'Нужно выбрать хотя бы один тэг' });
      }

      const content = form.querySelector('[name="content"]');
      if (!content || !content.value.trim()) {
        errors.push({ field: content, message: 'Содержание не заполнено' });
      }

      if (errors.length) {
        for (const err of errors) {
          if (err.field) err.field.classList.add('field--error');

          const toast = document.createElement('div');
          toast.className = 'toast';
          toast.textContent = err.message;
          toastContainer.append(toast);

          setTimeout(() => {
            toast.classList.add('toast--hide');
            toast.addEventListener('transitionend', () => toast.remove());
          }, 4000);
        }
      } else {
        form.submit();
      }
    });
  }
});
