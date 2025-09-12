<?php
  $user        = \App\Lib\Auth::currentUser();
  $userRoles   = $user['roles'] ?? [];
  $isAdmin     = in_array('admin', $userRoles, true);
  $showDeleted = !empty($_GET['show_deleted']);
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Управление пользователями</h2>

<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<?php if (!$isAdmin): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к управлению пользователями.
  </div>
<?php else: ?>

  <nav class="admin-filter" aria-label="фильтр по статусу">
    <a class="admin-filter__link <?= !$showDeleted ? 'is-active' : '' ?>"
       href="/users">
      <i class="fa-solid fa-user"></i> Активные
    </a>
    |
    <a class="admin-filter__link <?= $showDeleted ? 'is-active' : '' ?>"
       href="/users?show_deleted=1">
      <i class="fa-solid fa-trash"></i> Удалённые
    </a>
  </nav>

  <div class="filters-row" style="margin-block:1rem 1.5rem">
    <input
      type="text"
      id="user-search"
      class="filter-input"
      placeholder="Поиск по имени…"
      autocomplete="off"
    >
  </div>

  <div class="admin-grid admin-grid--cols-8" id="user-list">
    <div class="admin-grid__header">ID</div>
    <div class="admin-grid__header">Имя</div>
    <div class="admin-grid__header">Роли</div>
    <div class="admin-grid__header">Создан</div>
    <div class="admin-grid__header">Добавить роль</div>
    <div class="admin-grid__header">Удалить роль</div>
    <div class="admin-grid__header">Сменить пароль</div>
    <div class="admin-grid__header">
      <?= $showDeleted ? 'Восстановить' : 'Действия' ?>
    </div>

    <?php foreach ($users as $u): ?>
      <div class="admin-grid__row">
        <div class="admin-grid__cell" data-label="ID"><?= $u['id'] ?></div>

        <div class="admin-grid__cell" data-label="Имя">
          <?= htmlspecialchars($u['username']) ?>
          <?php if ($u['is_deleted']): ?>
            <i class="fa-solid fa-trash" style="color:var(--clr-accent);"></i>
          <?php endif; ?>
        </div>

        <div class="admin-grid__cell" data-label="Роли">
          <?php if (!empty($u['roles'])): ?>
            <?= htmlspecialchars(implode(', ', $u['roles'])) ?>
          <?php else: ?>
            <span class="tag">user</span>
          <?php endif; ?>
        </div>

        <div class="admin-grid__cell" data-label="Создан">
          <?= htmlspecialchars($u['created_at']) ?>
        </div>

        <?php if (!$u['is_deleted']): ?>
          <div class="admin-grid__cell" data-label="Добавить роль">
            <form class="inline-form" method="POST" action="/users">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action"  value="add_role">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <select name="new_role" class="filter-select" required>
                <?php foreach ($allRoles as $r): ?>
                  <?php if (empty($u['roles']) || !in_array($r, $u['roles'], true)): ?>
                    <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
              <button class="link-button" type="submit" title="Добавить">
                <i class="fa-solid fa-plus"></i>
              </button>
            </form>
          </div>

          <div class="admin-grid__cell" data-label="Удалить роль">
            <form class="inline-form" method="POST" action="/users">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action"  value="remove_role">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <select name="remove_role" class="filter-select" required>
                <?php foreach ($u['roles'] as $r): ?>
                  <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="link-button" type="submit" title="Удалить">
                <i class="fa-solid fa-minus"></i>
              </button>
            </form>
          </div>

          <div class="admin-grid__cell" data-label="Сменить пароль">
            <form class="inline-form" method="POST" action="/users" onsubmit="return confirm('Сменить пароль?')">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action"  value="reset_password">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="password"
                     name="new_password"
                     class="form-input"
                     placeholder="Новый пароль"
                     required>
              <button class="link-button" type="submit" title="Сменить пароль">
                <i class="fa-solid fa-key"></i>
              </button>
            </form>
          </div>

          <div class="admin-grid__cell actions" data-label="Действия">
            <form class="inline-form" method="POST" action="/users" onsubmit="return confirm('Удалить пользователя?')">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action"  value="delete_user">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="link-button" type="submit" title="Удалить">
                <i class="fa-solid fa-trash"></i>
              </button>
            </form>
          </div>
        <?php else: ?>
          <div class="admin-grid__cell" data-label="Добавить роль"></div>
          <div class="admin-grid__cell" data-label="Удалить роль"></div>
          <div class="admin-grid__cell" data-label="Сменить пароль"></div>
          <div class="admin-grid__cell actions" data-label="Восстановить">
            <form class="inline-form" method="POST" action="/users" onsubmit="return confirm('Восстановить пользователя?')">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action"  value="restore_user">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="btn btn--secondary" type="submit" title="Восстановить">
                <i class="fa-solid fa-rotate-left"></i> Восстановить
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>

  <script src="/assets/admin_users.js" defer></script>

<?php endif; ?>
