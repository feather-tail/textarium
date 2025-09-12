<?php
  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $isModerator = in_array('moderator', $userRoles, true);
  $isAdmin     = in_array('admin',    $userRoles, true);
  $canAdmin    = $isModerator || $isAdmin;

  $currentStatus = $_GET['status'] ?? 'approved';
?>

<?php if (!$canAdmin): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к административной панели.
  </div>
<?php else: ?>

<h2 class="page-title">Панель администратора</h2>

<?php include __DIR__ . '/partials/nav_admin.php'; ?>

<nav class="admin-filter" aria-label="Фильтр статей по статусу">
  <a href="/admin?status=approved"
     class="admin-filter__link<?= $currentStatus === 'approved' ? ' is-active' : '' ?>">
    <i class="fa-solid fa-check"></i> Опубликованные
  </a>
  |
  <a href="/admin?status=pending"
     class="admin-filter__link<?= $currentStatus === 'pending' ? ' is-active' : '' ?>">
    <i class="fa-solid fa-clock"></i> На модерации
  </a>
  |
  <a href="/admin?status=deleted&amp;show_deleted=1"
     class="admin-filter__link<?= $currentStatus === 'deleted' ? ' is-active' : '' ?>">
    <i class="fa-solid fa-trash"></i> Удалённые
  </a>
</nav>

<div class="admin-grid admin-grid--cols-7">
  <div class="admin-grid__header">ID</div>
  <div class="admin-grid__header">Заголовок</div>
  <div class="admin-grid__header">Категории</div>
  <div class="admin-grid__header">Теги</div>
  <div class="admin-grid__header">Создано</div>
  <div class="admin-grid__header">Статус</div>
  <div class="admin-grid__header">Действия</div>

  <?php foreach ($articles as $a): ?>
    <?php ob_start(); ?>
      <form action="/article/<?= $a['id'] ?>/delete" method="POST" style="display:inline;">
        <?= \App\Lib\Csrf::input() ?>
        <button type="submit" class="link-button" title="Удалить">
          <i class="fa-solid fa-trash"></i>
        </button>
      </form>
    <?php $deleteForm = ob_get_clean(); ?>

    <?php ob_start(); ?>
      <form action="/restore" method="POST" style="display:inline;" onsubmit="return confirm('Восстановить статью?')">
        <?= \App\Lib\Csrf::input() ?>
        <input type="hidden" name="id" value="<?= $a['id'] ?>">
        <button type="submit" class="link-button" title="Восстановить">
          <i class="fa-solid fa-arrow-rotate-left"></i>
        </button>
      </form>
    <?php $restoreForm = ob_get_clean(); ?>

    <div class="admin-grid__row">
      <div class="admin-grid__cell" data-label="ID"><?= $a['id'] ?></div>

      <div class="admin-grid__cell" data-label="Заголовок">
        <a href="/article/<?= $a['id'] ?>-<?= urlencode($a['slug']) ?>">
          <?= htmlspecialchars($a['title']) ?>
        </a>
      </div>

      <div class="admin-grid__cell" data-label="Категории">
        <?php foreach ($allCategories[$a['id']] ?? [] as $cat): ?>
          <a href="/?category[]=<?= $cat['id'] ?>"
             class="tag tag-category tag--link"
             title="Показать статьи из категории «<?= htmlspecialchars($cat['name']) ?>»">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-grid__cell" data-label="Теги">
        <?php foreach ($allTags[$a['id']] ?? [] as $tag): ?>
          <a href="/?tag[]=<?= $tag['id'] ?>"
             class="tag tag--link"
             title="Показать статьи с тегом «<?= htmlspecialchars($tag['name']) ?>»">
            #<?= htmlspecialchars($tag['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-grid__cell" data-label="Создано">
        <time datetime="<?= htmlspecialchars($a['created_at']) ?>">
          <?= htmlspecialchars($a['created_at']) ?>
        </time>
      </div>

      <div class="admin-grid__cell status" data-label="Статус">
        <?php
          echo match ($a['status']) {
            'approved' => '<i class="fa-solid fa-check"></i> Опубликовано',
            'pending'  => '<i class="fa-solid fa-clock"></i> На&nbsp;модерации',
            'draft'    => '<i class="fa-solid fa-pencil"></i> Черновик',
            'deleted'  => '<i class="fa-solid fa-trash"></i> Удалено',
            default    => '<i class="fa-solid fa-circle-question"></i> Неизвестно',
          };
        ?>
      </div>

      <div class="admin-grid__cell actions" data-label="Действия">
        <?php if ($a['status'] !== 'deleted'): ?>
          <a href="/admin/edit?id=<?= $a['id'] ?>" title="Редактировать">
            <i class="fa-solid fa-pen"></i>
          </a>

          <?php if ($a['status'] === 'pending'): ?>
            <form action="/approve" method="POST" style="display:inline;">
              <?= \App\Lib\Csrf::input() ?>
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button type="submit" class="link-button" title="Одобрить">
                <i class="fa-solid fa-circle-check"></i>
              </button>
            </form>
          <?php endif; ?>

          <?= $deleteForm ?>
        <?php else: ?>
          <?= $restoreForm ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>

<?php endif; ?>
