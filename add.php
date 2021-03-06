<?php
// Задаємо заголовок сторінки.
$page_title = 'Add new article';

require('base/header.php');

// Якщо на сторінку зайшов НЕ редактор, тоді даємо у відповідь статус 403 та пишемо повідомлення.
if (!$editor) {
  header('HTTP/1.1 403 Unauthorized');
  print 'Доступ заборонено.';
  // Підключаємо футер та припиняємо роботу скрипта.
  require('base/footer.php');
  exit;
}

// Підключення БД, адже нам необхідне підключення для створення статті.
require('base/db.php');

// Якщо ми отримали дані з ПОСТа, тоді обробляємо їх та вставляємо.
if (isset($_POST['submit'])) {

  try {
    $stmt = $conn->prepare('INSERT INTO content VALUES(NULL, :title, :short_desc, :full_desc, :timestamp)');

    // Обрізаємо усі теги у загловку.
    $stmt->bindParam(':title', strip_tags($_POST['title']));

    // Екрануємо теги у полях короткого та повного опису.
    $stmt->bindParam(':short_desc', htmlspecialchars($_POST['short_desc']));
    $stmt->bindParam(':full_desc', htmlspecialchars($_POST['full_desc']));

    // Беремо дату та час, переводимо у UNIX час.
    $date = "{$_POST['date']}  {$_POST['time']}";
    $stmt->bindParam(':timestamp', strtotime($date));
    // Виконуємо запит, результат запиту знаходиться у змінні $status.
    // Якщо $status рівне TRUE, тоді запит відбувся успішно.
    $status = $stmt->execute();

  } catch(PDOException $e) {
    // Виводимо на екран помилку.
    print "ERROR: {$e->getMessage()}";
    // Закриваємо футер.
    require('base/footer.php');
    // Зупиняємо роботу скрипта.
    exit;
  }

  // При успішному запиту перенаправляємо користувача на сторінку перегляду статті.
  if ($status) {
    // За допомогою методу lastInsertId() ми маємо змогу отрмати ІД статті, що була вставлена.
    header("Location: article.php?id={$conn->lastInsertId()}");
    exit;
  }
  else {
    // Вивід повідомлення про невдале додавання матеріалу.
    print "Запис не був доданий.";
  }
}
?>
<!-- Пишемо форму, метод ПОСТ, форма відправляє данні на цей же скрипт. -->

<div class="container container2">

  <form role="form" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST">
    <div class="form-group">

      <label for="title">Заголовок</label>
      <input class="form-control" type="text" name="title" id="title" required maxlength="255">

      <label for="short_desc">Короткий зміст</label>
      <textarea class="form-control" name="short_desc" id="short_desc" required maxlength="600"></textarea>

      <label for="full_desc">Повний зміст</label>
      <textarea class="form-control full-desc" name="full_desc" id="full_desc" required></textarea>

      <label for="date">День створення</label>
      <input class="form-control" type="date" name="date" id="date" required value="<?php print date('Y-m-d')?>">
      <label for="time">Час створення</label>
      <input class="form-control" type="time" name="time" id="time" required value="<?php print date('G:i')?>">

      <input class="btn btn-info" type="submit" name="submit" value="Зберегти">

    </div>

  </form>
</div>

<?php
// Підключаємо футер сайту.
require('base/footer.php');
?>
