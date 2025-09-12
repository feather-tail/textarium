<?php
  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $isModerator   = in_array('moderator', $userRoles, true);
  $isAdmin       = in_array('admin',     $userRoles, true);
  $canManageTags = $isModerator || $isAdmin;
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>
<h2 class="page-title">Теги</h2>
<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<?php if (!$canManageTags): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к управлению тегами.
  </div>
<?php else: ?>

  <?php if (!empty($errors)): ?>
    <div class="form-errors" role="alert">
      <?php foreach ($errors as $e): ?>
        <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="tag-form">
    <?= \App\Lib\Csrf::input() ?>
    <input type="text" name="name" placeholder="Новый тег" required>
    <input type="hidden" name="action" value="create">
    <button type="submit" class="btn btn--icon" title="Добавить">
      <i class="fa-solid fa-plus"></i>
    </button>
  </form>

  <hr>

  <h3>Активные теги</h3>
  <?php if (empty($tags)): ?>
    <p>Нет активных тегов.</p>
  <?php else: ?>
    <div class="admin-grid admin-grid--cols-3">
      <div class="admin-grid__header">ID</div>
      <div class="admin-grid__header">Название</div>
      <div class="admin-grid__header">Действия</div>

      <?php foreach ($tags as $tag): ?>
        <div class="admin-grid__row">
          <div class="admin-grid__cell" data-label="ID"><?= $tag['id'] ?></div>

          <div class="admin-grid__cell" data-label="Название">
            <form method="POST" action="/admin/tags" class="inline-form">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= $tag['id'] ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($tag['name']) ?>" required>
              <button type="submit" class="link-button" title="Сохранить">
                <i class="fa-solid fa-floppy-disk"></i>
              </button>
            </form>
          </div>

          <div class="admin-grid__cell actions" data-label="Действия">
            <form method="POST"
                  action="/admin/tags"
                  class="inline-form"
                  onsubmit="return confirm('Скрыть тег?')">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $tag['id'] ?>">
              <button type="submit" class="link-button" title="Скрыть">
                <i class="fa-solid fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($deletedTags)): ?>
    <hr>
    <h3>Удалённые теги</h3>
    <div class="admin-grid admin-grid--cols-3">
      <div class="admin-grid__header">ID</div>
      <div class="admin-grid__header">Название</div>
      <div class="admin-grid__header">Восстановить</div>

      <?php foreach ($deletedTags as $tag): ?>
        <div class="admin-grid__row">
          <div class="admin-grid__cell" data-label="ID"><?= $tag['id'] ?></div>
          <div class="admin-grid__cell" data-label="Название"><?= htmlspecialchars($tag['name']) ?></div>
          <div class="admin-grid__cell actions" data-label="Восстановить">
            <form method="POST"
                  action="/admin/tags"
                  class="inline-form"
                  onsubmit="return confirm('Восстановить тег?')">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="action" value="restore">
              <input type="hidden" name="id" value="<?= $tag['id'] ?>">
              <button type="submit" class="link-button" title="Восстановить">
                <i class="fa-solid fa-arrow-rotate-left"></i>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>
