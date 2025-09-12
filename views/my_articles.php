<?php
  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $canManage = array_intersect($userRoles, ['verified', 'moderator', 'admin']);
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Мои статьи</h2>

<?php include __DIR__ . '/partials/nav_profile.php'; ?>

<?php if (empty($canManage)): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к управлению статьями. Только верифицированные пользователи могут создавать и управлять своими статьями.
  </div>
<?php elseif (empty($articles)): ?>
  <p class="no-results">
    <i class="fa-solid fa-circle-info"></i> У вас пока нет статей.
  </p>
<?php else: ?>
  <ul class="article-list">
    <?php foreach ($articles as $a): ?>
      <?php
        $isOwner = isset($user['id']) && $user['id'] === $a['author_id'];
        $canEditDelete = $isOwner && !empty($canManage) && in_array($a['status'], ['draft', 'pending'], true);
      ?>

      <?php ob_start(); ?>
        <form class="inline-form"
              action="/article/<?= $a['id'] ?>/delete"
              method="POST"
              onsubmit="return confirm('Удалить статью?')">
          <?= \App\Lib\Csrf::input() ?>
          <button type="submit"
                  class="link-button article-action"
                  title="Удалить">
            <i class="fa-solid fa-trash"></i>
          </button>
        </form>
      <?php $deleteForm = ob_get_clean(); ?>

      <li class="article-card">
        <a class="article-card__title"
           href="/article/<?= $a['id'] ?>-<?= urlencode($a['slug']) ?>">
          <?= htmlspecialchars($a['title']) ?>
        </a>

        <div class="article-card__meta">
          <span class="meta-label">
            <i class="fa-solid fa-calendar"></i> Создано:
          </span>
          <time datetime="<?= $a['created_at'] ?>">
            <?= htmlspecialchars($a['created_at']) ?>
          </time>
        </div>

        <div class="article-card__meta">
          <span class="status-badge status--<?= $a['status'] ?>">
            <?php
              echo match ($a['status']) {
                'approved' => '<i class="fa-solid fa-check"></i> Опубликовано',
                'pending'  => '<i class="fa-regular fa-hourglass"></i> На модерации',
                'draft'    => '<i class="fa-solid fa-file-pen"></i> Черновик',
                'deleted'  => '<i class="fa-solid fa-trash"></i> Удалено',
                default    => 'Неизвестно',
              };
            ?>
          </span>
        </div>

        <?php if (!empty($allTags[$a['id']])): ?>
          <div class="article-card__meta">
            <?php foreach ($allTags[$a['id']] as $tag): ?>
              <span class="tag">
                #<?= htmlspecialchars($tag['name']) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($canEditDelete): ?>
          <div class="article__actions">
            <a class="article-action"
               href="/article/<?= $a['id'] ?>/edit"
               title="Редактировать">
              <i class="fa-solid fa-pen"></i>
            </a>
            <?= $deleteForm ?>
          </div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <?= \App\Lib\PaginationHelper::render($page, $perPage, $total, $_GET) ?>
<?php endif; ?>
