<?php
use App\Lib\ArticleStatus;

$user = \App\Lib\Auth::currentUser();
$userRoles = $user['roles'] ?? [];
$isModerator = in_array('moderator', $userRoles, true);
$isAdmin = in_array('admin', $userRoles, true);
$canSearch = $isModerator || $isAdmin;

$statusOptions = [
    ArticleStatus::APPROVED => 'Опубликовано',
    ArticleStatus::PENDING  => 'На модерации',
    ArticleStatus::DRAFT    => 'Черновик',
    ArticleStatus::DELETED  => 'Удалено',
];

$statusIcons = [
    ArticleStatus::APPROVED => 'fa-check',
    ArticleStatus::PENDING  => 'fa-hourglass-half',
    ArticleStatus::DRAFT    => 'fa-pencil',
    ArticleStatus::DELETED  => 'fa-trash',
];
?>
<?php include __DIR__ . '/partials/back_button.php'; ?>
<h2 class="page-title">Расширенный поиск</h2>
<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<?php if (!$canSearch): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к расширенному поиску.
  </div>
<?php else: ?>

<form method="GET" class="filter-form" role="search">
  <div class="filters-row">
    <label class="form-field">
      <span class="form-field__label">По тексту</span>
      <input type="text" name="query"
             class="form-input"
             value="<?= htmlspecialchars($_GET['query'] ?? '') ?>"
             placeholder="Заголовок или содержимое…">
    </label>

    <label class="form-field">
      <span class="form-field__label">Категория</span>
      <select name="category_id" class="form-input">
        <option value="0">Все</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($filters['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="form-field">
      <span class="form-field__label">Тег</span>
      <select name="tag_id" class="form-input">
        <option value="0">Все</option>
        <?php foreach ($tags as $t): ?>
          <option value="<?= $t['id'] ?>" <?= ($filters['tag_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="form-field">
      <span class="form-field__label">Автор</span>
      <select name="author_id" class="form-input">
        <option value="0">Все</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filters['author_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label class="form-field">
      <span class="form-field__label">Статус</span>
      <select name="status" class="form-input">
        <option value="">Все</option>
        <?php foreach ($statusOptions as $value => $label): ?>
          <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
            <?= $label ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <button type="submit" class="btn btn--icon" style="align-self:flex-end">
      <i class="fa-solid fa-magnifying-glass"></i>
      <span class="visually-hidden">Найти</span>
    </button>
  </div>
</form>

<?php if (empty($articles)): ?>
  <p class="no-results"><i class="fa-solid fa-circle-info"></i> Ничего не найдено.</p>
<?php else: ?>
  <ul class="article-list">
    <?php foreach ($articles as $a): ?>
      <li class="article-card">
        <a href="/article/<?= $a['id'] ?>-<?= urlencode($a['slug']) ?>"
           class="article-card__title">
          <?= htmlspecialchars($a['title']) ?>
        </a>

        <div class="article-card__meta">
          <span class="meta-label">Автор:</span>
          <?= htmlspecialchars($a['author_username']) ?>
          &nbsp;|&nbsp;
          <span class="meta-label">Статус:</span>
          <i class="fa-solid <?= $statusIcons[$a['status']] ?? 'fa-circle-question' ?>"></i>
          <?= $statusOptions[$a['status']] ?? 'Неизвестно' ?>
          &nbsp;|&nbsp;
          <?= $a['created_at'] ?>
        </div>

        <div class="article__actions">
          <a href="/article/<?= $a['id'] ?>-<?= urlencode($a['slug']) ?>"
             class="article__action" title="Просмотр">
            <i class="fa-solid fa-file-lines"></i>
          </a>
          <a href="/admin/edit?id=<?= $a['id'] ?>"
             class="article__action" title="Редактировать">
            <i class="fa-solid fa-pen"></i>
          </a>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>
<?php endif; ?>

<?php endif; ?>
