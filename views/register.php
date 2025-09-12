<?php include __DIR__ . '/partials/back_button.php'; ?>

<h2 class="page-title">Регистрация</h2>

<?php if (!empty($errors)): ?>
  <div class="form-errors" role="alert">
    <?php foreach ($errors as $e): ?>
      <p><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($e) ?></p>
    <?php endforeach ?>
  </div>
<?php endif; ?>

<form method="POST" class="auth-form">
  <?= \App\Lib\Csrf::input() ?>

  <label class="form-field">
    <span class="form-field__label">Логин</span>
    <input
      type="text"
      name="username"
      class="form-input"
      value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES | ENT_HTML5) ?>"
      minlength="3"
      maxlength="50"
      pattern="[A-Za-zА-Яа-яЁё0-9 ]+"
      title="Только буквы, цифры и пробелы"
      autocomplete="username"
      required
    >
  </label>

  <label class="form-field">
    <span class="form-field__label">E-mail</span>
    <input
      type="email"
      name="email"
      class="form-input"
      maxlength="50"
      value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_HTML5) ?>"
      autocomplete="email"
      required
    >
  </label>

  <label class="form-field">
    <span class="form-field__label">Пароль</span>
    <input
      type="password"
      name="password"
      class="form-input"
      minlength="6"
      maxlength="100"
      autocomplete="new-password"
      required
    >
  </label>

  <label class="form-field">
    <span class="form-field__label">Повторите пароль</span>
    <input
      type="password"
      name="confirm"
      class="form-input"
      minlength="6"
      maxlength="100"
      autocomplete="new-password"
      required
    >
  </label>

  <div class="form-actions">
    <button type="submit" class="btn">
      <i class="fa-solid fa-user-plus"></i> Зарегистрироваться
    </button>
  </div>
</form>
