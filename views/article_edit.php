<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Редактировать статью</h2>

<?php if (!empty($errors)): ?>
  <div class="form-errors" role="alert">
    <?php foreach ($errors as $e): ?>
      <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($e) ?></p>
    <?php endforeach ?>
  </div>
<?php endif; ?>

<?php
  use App\Lib\ArticleStatus;
  $hasDraft = (isset($article['status']) && $article['status'] === ArticleStatus::DRAFT);
  $formAttr = $hasDraft ? 'data-draft-id="' . (int) $article['id'] . '"' : '';
?>

<form method="POST" action="<?= $hasDraft ? '/edit?id=' . $article['id'] : '/article/' . $article['id'] . '/edit' ?>" class="article-form" <?= $formAttr ?>>
  <input type="hidden" name="csrf_token" id="csrf-token" value="<?= \App\Lib\Csrf::token() ?>">

  <label class="form-field">
    <span class="form-field__label">Заголовок</span>
    <input
      type="text"
      name="title"
      maxlength="255"
      data-maxlength="255"
      required
      value="<?= htmlspecialchars($article['title']) ?>"
      class="form-input"
    >
  </label>

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
    <textarea
      name="content"
      id="content"
      maxlength="20000"
      data-maxlength="20000"
      rows="15"
    ><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
  </label>

  <fieldset class="form-group">
    <legend class="form-group__legend">
      <i class="fa-solid fa-folder-tree"></i> Категории
    </legend>
    <div class="checkbox-grid">
      <?php foreach ($categories as $c): ?>
        <label class="form-checkbox">
          <input
            type="checkbox"
            name="categories[]"
            value="<?= $c['id'] ?>"
            <?= in_array($c['id'], $selectedCategoryIds) ? 'checked' : '' ?>
          >
          <span><?= htmlspecialchars($c['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <legend class="form-group__legend">
      <i class="fa-solid fa-tags"></i> Теги
    </legend>
    <div class="checkbox-grid">
      <?php foreach ($tags as $t): ?>
        <label class="form-checkbox">
          <input
            type="checkbox"
            name="tags[]"
            value="<?= $t['id'] ?>"
            <?= in_array($t['id'], $selectedTagIds) ? 'checked' : '' ?>
          >
          <span>#<?= htmlspecialchars($t['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <label class="form-checkbox">
    <input type="checkbox" name="is_draft" <?= $hasDraft ? 'checked' : '' ?>>
    <span>Сохранить как черновик</span>
  </label>

  <div class="form-actions">
    <button type="submit" class="btn">
      <i class="fa-solid fa-floppy-disk"></i> Сохранить изменения
    </button>
  </div>
</form>

<script type="module" src="/assets/form-validation.js"></script>
<script type="module" src="/assets/editor/editor.js"></script>
