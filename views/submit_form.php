<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Создать статью</h2>

<?php if (!empty($errors)): ?>
  <div class="form-errors" role="alert">
    <?php foreach ($errors as $err): ?>
      <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($err) ?></p>
    <?php endforeach ?>
  </div>
<?php endif; ?>

<form
  method="POST"
  class="article-form"
  <?= isset($article['id']) ? 'data-draft-id="' . (int) $article['id'] . '"' : '' ?>
>
  <input type="hidden" name="csrf_token" id="csrf-token" value="<?= \App\Lib\Csrf::token() ?>">
  <input type="hidden" name="client_action" class="js-client-action" value="">

  <label class="form-field">
    <span class="form-field__label">Заголовок</span>
    <input
      id="title"
      class="form-input"
      type="text"
      name="title"
      maxlength="255"
      data-maxlength="255"
      required
      value="<?= htmlspecialchars($article['title'] ?? '') ?>"
    >
  </label>

  <fieldset class="form-group">
    <legend class="form-group__legend">
      <i class="fa-solid fa-folder-tree"></i> Категории
    </legend>
    <div class="checkbox-grid">
      <?php foreach ($categories as $cat): ?>
        <label class="form-checkbox">
          <input
            type="checkbox"
            name="categories[]"
            value="<?= $cat['id'] ?>"
            <?= !empty($selectedCategoryIds) && in_array($cat['id'], $selectedCategoryIds) ? 'checked' : '' ?>
          >
          <span><?= htmlspecialchars($cat['name']) ?></span>
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
          >
          <span>#<?= htmlspecialchars($t['name']) ?></span>
        </label>
      <?php endforeach ?>
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
    <textarea
      name="content"
      id="content"
      rows="12"
      maxlength="20000"
      data-maxlength="20000"
    ><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
  </label>

  <div class="form-actions">
    <button type="submit" name="action" value="publish" class="btn">
      <i class="fa-solid fa-paper-plane"></i> Опубликовать
    </button>
    <button type="submit" name="action" value="draft" class="btn btn--secondary">
      <i class="fa-solid fa-floppy-disk"></i> Сохранить как черновик
    </button>
  </div>

  <script>
    const fld = document.querySelector('.js-client-action');
    document.querySelector('button[name="action"][value="publish"]')
      .addEventListener('click', () => fld.value = 'publish');
    document.querySelector('button[name="action"][value="draft"]')
      .addEventListener('click', () => fld.value = 'draft');
  </script>
</form>

<script type="module" src="/assets/form-validation.js"></script>
<script type="module" src="/assets/editor/editor.js"></script>
