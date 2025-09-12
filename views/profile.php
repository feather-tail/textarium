<?php
$user = \App\Lib\Auth::currentUser();
$userRoles = $user['roles'] ?? [];
$canChangePassword = array_intersect($userRoles, ['user', 'verified', 'moderator', 'admin']);
?>

<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title"><div>Профиль </div><div class="page-title__username"><?= htmlspecialchars($user['username']) ?></div></h2>

<?php include __DIR__ . '/partials/nav_profile.php'; ?>

<?php if (empty($canChangePassword)): ?>
  <div class="no-access">
    <i class="fa-solid fa-circle-exclamation"></i>
    У вас нет доступа к просмотру профиля и смене пароля.
  </div>
<?php else: ?>

<section class="profile-info">
  <p>
    <strong>Роли:</strong>
    <?php foreach ($user['roles'] ?? [] as $role): ?>
      <span class="tag tag--role"><?= htmlspecialchars($role) ?></span>
    <?php endforeach; ?>
  </p>
</section>

<?php endif; ?>
