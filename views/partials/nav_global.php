<?php
  $activePage = $activePage ?? '';

  if (!isset($isLoggedIn)) {
    $isLoggedIn = \App\Lib\Auth::isLoggedIn();
  }
  if (!isset($userRoles)) {
    $user = \App\Lib\Auth::currentUser();
    $userRoles = $user['roles'] ?? [];
  }

  $canCreate   = array_intersect($userRoles, ['verified', 'moderator', 'admin']);
  $isModerator = in_array('moderator', $userRoles, true);
  $isAdmin     = in_array('admin', $userRoles, true);
?>

<ul class="nav-global__list" role="menubar">
  <li class="nav-global__item" role="none">
    <a
      href="/"
      class="nav-global__link<?= $activePage === 'home' ? ' is-active' : '' ?>"
      role="menuitem"
      <?= $activePage === 'home' ? 'aria-current="page"' : '' ?>
    >
      <i class="fa-solid fa-house" aria-hidden="true"></i>
      <span class="nav-global__text">Библиотека</span>
    </a>
  </li>

  <li class="nav-global__item" role="none">
    <a
      href="https://lastcity.ru"
      class="nav-global__link"
      role="menuitem"
    >
      <i class="fa-solid fa-scroll" aria-hidden="true"></i>
      <span class="nav-global__text">Форум</span>
    </a>
  </li>

  <?php if ($isLoggedIn): ?>
    <?php if (!empty($canCreate)): ?>
      <li class="nav-global__item" role="none">
        <a
          href="/submit"
          class="nav-global__link<?= $activePage === 'submit' ? ' is-active' : '' ?>"
          role="menuitem"
          <?= $activePage === 'submit' ? 'aria-current="page"' : '' ?>
        >
          <i class="fa-solid fa-pen" aria-hidden="true"></i>
          <span class="nav-global__text">Создать статью</span>
        </a>
      </li>
    <?php endif; ?>

    <li class="nav-global__item" role="none">
      <a
        href="/profile"
        class="nav-global__link<?= $activePage === 'profile' ? ' is-active' : '' ?>"
        role="menuitem"
        <?= $activePage === 'profile' ? 'aria-current="page"' : '' ?>
      >
        <i class="fa-solid fa-user" aria-hidden="true"></i>
        <span class="nav-global__text">Профиль</span>
      </a>
    </li>

    <?php if ($isModerator || $isAdmin): ?>
      <li class="nav-global__item" role="none">
        <a
          href="/admin"
          class="nav-global__link<?= $activePage === 'admin' ? ' is-active' : '' ?>"
          role="menuitem"
          <?= $activePage === 'admin' ? 'aria-current="page"' : '' ?>
        >
          <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
          <span class="nav-global__text">Админка</span>
        </a>
      </li>
    <?php endif; ?>

  <?php else: ?>
    <li class="nav-global__item" role="none">
      <a
        href="/register"
        class="nav-global__link<?= $activePage === 'register' ? ' is-active' : '' ?>"
        role="menuitem"
        <?= $activePage === 'register' ? 'aria-current="page"' : '' ?>
      >
        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
        <span class="nav-global__text">Регистрация</span>
      </a>
    </li>
  <?php endif; ?>
</ul>
