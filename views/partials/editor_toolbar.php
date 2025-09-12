<div id="editor-toolbar">

  <!-- Стиль текста -->
  <div class="bb-group">
    <?php
      $textButtons = [
        ['b', 'fa-bold', 'Жирный'],
        ['i', 'fa-italic', 'Курсив'],
        ['u', 'fa-underline', 'Подчёркнутый'],
        ['s', 'fa-strikethrough', 'Зачёркнутый'],
        ['sub', 'fa-arrow-down-short-wide', 'Подстрочный текст'],
        ['sup', 'fa-arrow-up-short-wide', 'Надстрочный текст'],
        ['color', 'fa-palette', 'Цвет текста'],
        ['size', 'fa-text-height', 'Размер текста'],
        ['font', 'fa-font', 'Шрифт'],
      ];
      foreach ($textButtons as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Заголовки -->
  <div class="bb-group">
    <button type="button" class="bb-btn" data-tag="heading" title="Заголовок">
      <i class="fa fa-heading"></i>
    </button>
  </div>

  <!-- Выравнивание -->
  <div class="bb-group">
    <?php
      $align = [
        ['align', 'fa-align-justify', 'Выравнивание (выбор)'],
      ];
      foreach ($align as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Вставки -->
  <div class="bb-group">
    <?php
      $insert = [
        ['url', 'fa-link', 'Ссылка'],
        ['img', 'fa-image', 'Изображение'],
      ];
      foreach ($insert as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Цитаты и код -->
  <div class="bb-group">
    <?php
      $quotes = [
        ['quote', 'fa-quote-right', 'Цитата'],
        ['code', 'fa-code', 'Код'],
      ];
      foreach ($quotes as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Списки -->
  <div class="bb-group">
    <?php
      $lists = [
        ['list', 'fa-list-ul', 'Список'],
        ['list1', 'fa-list-ol', 'Нумерованный список'],
        ['*', 'fa-circle', 'Элемент списка'],
      ];
      foreach ($lists as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Таблицы -->
  <div class="bb-group">
    <?php
      $tables = [
        ['table', 'fa-table', 'Таблица'],
      ];
      foreach ($tables as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Специальные блоки -->
  <div class="bb-group">
    <?php
      $special = [
        ['spoiler', 'fa-eye-slash', 'Спойлер'],
      ];
      foreach ($special as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Структура -->
  <div class="bb-group">
    <?php
      $structure = [
        ['hr', 'fa-minus', 'Горизонтальная линия'],
        ['br', 'fa-arrow-turn-down', 'Перенос строки'],
      ];
      foreach ($structure as [$tag, $icon, $label]) {
        echo "<button type='button' class='bb-btn' data-tag='{$tag}' title='{$label}'><i class='fa {$icon}'></i></button>";
      }
    ?>
  </div>

  <!-- Очистить всё -->
  <div class="bb-group">
    <button type="button" class="bb-btn" id="clear-content" title="Очистить всё">
      <i class="fa fa-eraser"></i>
    </button>
  </div>

  <!-- Модальное окно -->
  <div id="bbcode-modal" class="bb-modal hidden">
    <div class="bb-modal-content">
      <p id="bbcode-modal-label">Введите значение</p>

      <input type="text" id="bbcode-modal-input" class="hidden" placeholder="">

      <select id="bbcode-modal-select" class="hidden"></select>

      <div id="bbcode-color-palette" class="color-palette hidden"></div>

      <div id="bbcode-align-buttons" class="align-options hidden">
        <button type="button" class="bb-align-option" data-align="left" title="Выровнять по левому краю">
          <i class="fa fa-align-left"></i>
        </button>
        <button type="button" class="bb-align-option" data-align="center" title="По центру">
          <i class="fa fa-align-center"></i>
        </button>
        <button type="button" class="bb-align-option" data-align="right" title="По правому краю">
          <i class="fa fa-align-right"></i>
        </button>
        <button type="button" class="bb-align-option" data-align="justify" title="По ширине">
          <i class="fa fa-align-justify"></i>
        </button>
      </div>

      <div class="bb-modal-actions">
        <button type="button" id="bbcode-modal-ok" class="hidden">OK</button>
        <button type="button" id="bbcode-modal-cancel">Отмена</button>
      </div>
    </div>
  </div>
</div>

<div id="bbcode-table-modal" class="bb-table-modal hidden">
  <button type="button" class="modal-close" id="table-modal-close">&times;</button>
  <div class="table-modal-content">
    <p id="table-size-label">0 x 0</p>
    <div id="table-grid-preview"></div>
  </div>
</div>

</div>
