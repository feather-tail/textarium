<?php

$errors = $errors ?? [];
include __DIR__ . '/partials/back_button.php';

$user = \App\Lib\Auth::currentUser();
$userRoles = $user['roles'] ?? [];
$isModerator = in_array('moderator', $userRoles, true);
$isAdmin = in_array('admin', $userRoles, true);
$canEdit = $isModerator || $isAdmin;
?>
<h2 class="page-title">Редактировать статью</h2>

<?php if (!$canEdit): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к административному редактированию статей.
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
  \App\Lib\ArticleStatus::DRAFT    => 'Черновик',
  \App\Lib\ArticleStatus::PENDING  => 'На модерации',
  \App\Lib\ArticleStatus::APPROVED => 'Опубликовано',
  \App\Lib\ArticleStatus::DELETED  => 'Удалено',
];
$categoryIds = array_column($selectedCategories ?? [], 'id');
$tagIds = array_column($selectedTags ?? [], 'id');
?>

<form method="POST" action="/admin/edit?id=<?= $article['id'] ?>" class="article-form">
  <?= \App\Lib\Csrf::input() ?>

  <label class="form-field">
    <span class="form-field__label">Автор</span>
    <select name="author_id" required>
      <?php foreach ($users as $u): ?>
        <?php
          $pdo = \App\Lib\Db::getConnection();
          $stmt = $pdo->prepare(
            'SELECT r.name FROM user_roles ur
             JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = ?'
          );
          $stmt->execute([$u['id']]);
          $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <option value="<?= $u['id'] ?>" <?= $article['author_id'] == $u['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars(implode(', ', $roles)) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label class="form-field">
    <span class="form-field__label">Заголовок</span>
    <input type="text"
           name="title"
           maxlength="255"
           data-maxlength="255"
           value="<?= htmlspecialchars($article['title']) ?>"
           required>
  </label>

  <fieldset class="form-group">
    <legend class="form-group__legend">Категории</legend>
    <div class="checkbox-grid">
      <?php foreach ($categories as $cat): ?>
        <label class="form-checkbox<?= !empty($cat['is_deleted']) ? ' is-inactive' : '' ?>">
          <input type="checkbox"
                name="categories[]"
                value="<?= $cat['id'] ?>"
                <?= in_array($cat['id'], $categoryIds) ? 'checked' : '' ?>>
          <span>
            <?= htmlspecialchars($cat['name']) ?>
            <?php if (!empty($cat['is_deleted'])): ?>
              <em style="color:#888;font-size:0.9em">(скрыта)</em>
            <?php endif; ?>
          </span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <legend class="form-group__legend">Теги</legend>
    <div class="checkbox-grid">
      <?php foreach ($tags as $t): ?>
        <label class="form-checkbox<?= !empty($t['is_deleted']) ? ' is-inactive' : '' ?>">
          <input type="checkbox"
                name="tags[]"
                value="<?= $t['id'] ?>"
                <?= in_array($t['id'], $tagIds) ? 'checked' : '' ?>>
          <span>
            #<?= htmlspecialchars($t['name']) ?>
            <?php if (!empty($t['is_deleted'])): ?>
              <em style="color:#888;font-size:0.9em">(скрыт)</em>
            <?php endif; ?>
          </span>
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
    <textarea name="content"
              id="content"
              maxlength="20000"
              data-maxlength="20000"
              rows="12"
              required><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
  </label>

  <label class="form-field">
    <span class="form-field__label">Статус</span>
    <select name="status">
      <?php foreach ($statusOptions as $key => $label): ?>
        <option value="<?= $key ?>" <?= $article['status'] === $key ? 'selected' : '' ?>>
          <?= $label ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <div class="form-actions">
    <button type="submit" class="btn">
      <i class="fa-solid fa-floppy-disk"></i> Сохранить
    </button>
  </div>
</form>

<script type="module" src="/assets/form-validation.js"></script>
<script type="module" src="/assets/editor/editor.js"></script>

<?php endif; ?>
