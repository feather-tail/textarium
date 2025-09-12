<?php
  use App\Lib\ArticleStatus;
  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $isModerator = in_array('moderator', $userRoles, true);
  $isAdmin = in_array('admin', $userRoles, true);
  $canCreate = $isModerator || $isAdmin;
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Создать статью</h2>
<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<?php if (!$canCreate): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к созданию статей в административном режиме.
  </div>
<?php else: ?>

<?php if (!empty($errors)): ?>
  <div class="form-errors" role="alert">
    <?php foreach ($errors as $err): ?>
      <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($err) ?></p>
    <?php endforeach ?>
  </div>
<?php endif; ?>

<?php

$statusOptions = [
  ArticleStatus::DRAFT    => 'Черновик',
  ArticleStatus::PENDING  => 'На модерации',
  ArticleStatus::APPROVED => 'Опубликовано',
];
?>

<form method="POST" class="article-form">
  <?= \App\Lib\Csrf::input() ?>

  <label class="form-field">
    <span class="form-field__label">Автор</span>
    <select name="author_id" required>
      <option disabled selected value="">Выберите автора</option>
      <?php foreach ($users as $u): ?>
        <?php
          $roleText = empty($u['roles']) ? 'user' : implode(', ', $u['roles']);
        ?>
        <option value="<?= $u['id'] ?>">
          <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($roleText) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label class="form-field form-field--title">
    <span class="form-field__label">Заголовок</span>
    <input type="text" name="title" maxlength="255"
          data-maxlength="255" required>
  </label>

  <fieldset class="form-group">
    <legend class="form-group__legend">Категории</legend>
    <div class="checkbox-grid">
      <?php foreach ($categories as $cat): ?>
        <label class="form-checkbox">
          <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
          <span><?= htmlspecialchars($cat['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <legend class="form-group__legend">Теги</legend>
    <div class="checkbox-grid">
      <?php foreach ($tags as $t): ?>
        <label class="form-checkbox">
          <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>">
          <span>#<?= htmlspecialchars($t['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <div class="editor-controls">
    <button type="button" id="preview-toggle" class="btn btn--icon">
      <i class="fa-solid fa-eye"></i>
      <span class="visually-hidden">Предпросмотр</span>
    </button>
  </div>
  <div id="bb-preview" class="bb-preview" hidden></div>

  <?php include __DIR__ . '/partials/editor_toolbar.php'; ?>

  <label class="form-field form-field--content">
    <span class="form-field__label">Содержание</span>
    <textarea name="content" id="content" rows="12" maxlength="20000" data-maxlength="20000"></textarea>
  </label>

  <label class="form-field">
    <span class="form-field__label">Статус</span>
    <select name="status">
      <?php foreach ($statusOptions as $key => $label): ?>
        <option value="<?= $key ?>"><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <div class="form-actions">
    <button type="submit" class="btn">
      <i class="fa-solid fa-paper-plane"></i> Создать
    </button>
  </div>
</form>

<script type="module" src="/assets/form-validation.js"></script>
<script type="module" src="/assets/editor/editor.js"></script>

<?php endif; ?>
