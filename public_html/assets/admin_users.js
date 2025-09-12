document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('user-list');
  const search = document.getElementById('user-search');
  let timer;

  function csrfInput() {
    const token = document.querySelector('input[name="csrf_token"]')?.value || '';
    return `<input type="hidden" name="csrf_token" value="${token}">`;
  }

  function renderGrid(usersArray, allRoles, total, perPage, currentPage, showDeleted) {
    const headers = [
      'ID',
      'Имя',
      'Роли',
      'Создан',
      'Добавить роль',
      'Удалить роль',
      'Сменить пароль',
      showDeleted ? 'Восстановить' : 'Действия'
    ].map(h => `<div class="admin-grid__header">${h}</div>`).join('');

    // Строки
    const rows = usersArray.map(u => {
      const roles = Array.isArray(u.roles)
        ? u.roles
        : (typeof u.roles === 'string' ? [u.roles] : []);
      const roleTags = roles.length
        ? roles.map(r => `<span class="tag">${r}</span>`).join(' ')
        : '<span class="tag">user</span>';
      const addRoleOptions = allRoles
        .filter(r => !roles.includes(r))
        .map(r => `<option value="${r}">${r}</option>`).join('');
      const removeRoleOptions = roles
        .map(r => `<option value="${r}">${r}</option>`).join('');

      if (u.is_deleted) {
        return `
          <div class="admin-grid__row">
            <div class="admin-grid__cell" data-label="ID">${u.id}</div>
            <div class="admin-grid__cell" data-label="Имя">
              ${u.username} <i class="fa-solid fa-trash" style="color:var(--clr-accent);"></i>
            </div>
            <div class="admin-grid__cell" data-label="Роли">${roleTags}</div>
            <div class="admin-grid__cell" data-label="Создан">${u.created_at}</div>
            <div class="admin-grid__cell" data-label="Добавить роль"></div>
            <div class="admin-grid__cell" data-label="Удалить роль"></div>
            <div class="admin-grid__cell" data-label="Сменить пароль"></div>
            <div class="admin-grid__cell actions" data-label="Восстановить">
              <form method="POST" class="inline-form" onsubmit="return confirm('Восстановить пользователя?')">
                ${csrfInput()}
                <input type="hidden" name="action" value="restore_user">
                <input type="hidden" name="user_id" value="${u.id}">
                <button class="btn btn--secondary" type="submit" title="Восстановить">
                  <i class="fa-solid fa-rotate-left"></i>
                </button>
              </form>
            </div>
          </div>
        `;
      }

      return `
        <div class="admin-grid__row">
          <div class="admin-grid__cell" data-label="ID">${u.id}</div>
          <div class="admin-grid__cell" data-label="Имя">${u.username}</div>
          <div class="admin-grid__cell" data-label="Роли">${roleTags}</div>
          <div class="admin-grid__cell" data-label="Создан">${u.created_at}</div>
          <div class="admin-grid__cell" data-label="Добавить роль">
            <form class="inline-form" method="POST" action="/users">
              ${csrfInput()}
              <input type="hidden" name="action" value="add_role">
              <input type="hidden" name="user_id" value="${u.id}">
              <select name="new_role" class="filter-select" required>
                ${addRoleOptions}
              </select>
              <button class="link-button" type="submit" title="Добавить">
                <i class="fa-solid fa-plus"></i>
              </button>
            </form>
          </div>
          <div class="admin-grid__cell" data-label="Удалить роль">
            <form class="inline-form" method="POST" action="/users">
              ${csrfInput()}
              <input type="hidden" name="action" value="remove_role">
              <input type="hidden" name="user_id" value="${u.id}">
              <select name="remove_role" class="filter-select" required>
                ${removeRoleOptions}
              </select>
              <button class="link-button" type="submit" title="Удалить">
                <i class="fa-solid fa-minus"></i>
              </button>
            </form>
          </div>
          <div class="admin-grid__cell" data-label="Сменить пароль">
            <form class="inline-form" method="POST" action="/users" onsubmit="return confirm('Сменить пароль?')">
              ${csrfInput()}
              <input type="hidden" name="action" value="reset_password">
              <input type="hidden" name="user_id" value="${u.id}">
              <input type="password" name="new_password" class="form-input" placeholder="Новый пароль" required>
              <button class="link-button" type="submit" title="Сменить пароль">
                <i class="fa-solid fa-key"></i>
              </button>
            </form>
          </div>
          <div class="admin-grid__cell actions" data-label="Действия">
            <form class="inline-form" method="POST" action="/users" onsubmit="return confirm('Удалить пользователя?')">
              ${csrfInput()}
              <input type="hidden" name="action" value="delete_user">
              <input type="hidden" name="user_id" value="${u.id}">
              <button class="link-button" type="submit" title="Удалить">
                <i class="fa-solid fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      `;
    }).join('');

    const totalPages = Math.ceil(total / perPage);
    let pagination = '';
    for (let i = 1; i <= totalPages; i++) {
      pagination += `<a href="#" data-page="${i}" class="pagination__link${i === currentPage ? ' is-current' : ''}">${i}</a> `;
    }

    return {
      gridHtml: headers + rows,
      paginationHtml: `<nav class="pagination">${pagination}</nav>`
    };
  }

  function loadUsers(page = 1) {
    const query = search.value;
    const params = new URLSearchParams(window.location.search);
    const showDeleted = params.get('show_deleted') === '1';
    let url = `/api/users?q=${encodeURIComponent(query)}&page=${page}`;
    if (showDeleted) url += '&show_deleted=1';

    fetch(url)
      .then(res => res.json())
      .then(response => {
        const attrs = response.data?.attributes || {};
        const usersArray  = Array.isArray(attrs.users) ? attrs.users : [];
        const allRoles    = Array.isArray(attrs.allRoles) ? attrs.allRoles : [];
        const total       = Number(attrs.total)   || 0;
        const perPage     = Number(attrs.perPage) || 1;
        const currentPage = Number(attrs.page)    || page;

        // Рендерим сетку и пагинацию
        const { gridHtml, paginationHtml } = renderGrid(
          usersArray,
          allRoles,
          total,
          perPage,
          currentPage,
          showDeleted
        );
        container.innerHTML = gridHtml;
        const oldNav = container.nextElementSibling;
        if (oldNav && oldNav.classList.contains('pagination')) oldNav.remove();
        container.insertAdjacentHTML('afterend', paginationHtml);
      })
      .catch(err => {
        console.error('Ошибка при загрузке пользователей:', err);
        container.innerHTML = '<p class="error">Не удалось загрузить список пользователей.</p>';
      });
  }

  if (search && container) {
    search.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => loadUsers(1), 400);
    });

    container.addEventListener('click', e => {
      if (e.target.tagName === 'A' && e.target.dataset.page) {
        e.preventDefault();
        loadUsers(Number(e.target.dataset.page));
      }
    });

    loadUsers();
  }
});
