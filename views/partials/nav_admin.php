<?php
  $activeAdmin = $activeAdmin
               ?? preg_replace('~^/admin/?~', '', strtok($_SERVER['REQUEST_URI'], '?')) ?: 'articles';

  $user = \App\Lib\Auth::currentUser();
  $userRoles = $user['roles'] ?? [];
  $isModerator = in_array('moderator', $userRoles, true);
  $isAdmin = in_array('admin', $userRoles, true);
?>

<ul class="admin-nav__list" role="menu">
  <li class="admin-nav__item" role="none">
    <a href="/admin"
       class="admin-nav__link<?= $activeAdmin === 'articles' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
      <span>Статьи</span>
    </a>
  </li>

  <li class="admin-nav__item" role="none">
    <a href="/admin/categories"
       class="admin-nav__link<?= $activeAdmin === 'categories' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-folder-tree" aria-hidden="true"></i>
      <span>Категории</span>
    </a>
  </li>

  <li class="admin-nav__item" role="none">
    <a href="/admin/tags"
       class="admin-nav__link<?= $activeAdmin === 'tags' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-tags" aria-hidden="true"></i>
      <span>Теги</span>
    </a>
  </li>

  <li class="admin-nav__item" role="none">
    <a href="/admin/search"
       class="admin-nav__link<?= $activeAdmin === 'search' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <span>Поиск</span>
    </a>
  </li>

  <li class="admin-nav__item" role="none">
    <a href="/admin/logs"
       class="admin-nav__link<?= $activeAdmin === 'logs' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-scroll" aria-hidden="true"></i>
      <span>Журнал</span>
    </a>
  </li>

  <?php if ($isAdmin): ?>
    <li class="admin-nav__item" role="none">
      <a href="/users"
         class="admin-nav__link<?= $activeAdmin === 'users' ? ' is-active' : '' ?>"
         role="menuitem"
      >
        <i class="fa-solid fa-users" aria-hidden="true"></i>
        <span>Пользователи</span>
      </a>
    </li>
  <?php endif; ?>

  <li class="admin-nav__item" role="none">
    <a href="/admin/create"
       class="admin-nav__link<?= $activeAdmin === 'create' ? ' is-active' : '' ?>"
       role="menuitem"
    >
      <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
      <span>Создать</span>
    </a>
  </li>
</ul>
