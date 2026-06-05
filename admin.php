<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'u82517';
        $username = 'u82517';
        $password = '2297334';
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка БД: " . $e->getMessage());
        }
    }
    return $pdo;
}

function authenticateAdmin() {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== 'admin' || $_SERVER['PHP_AUTH_PW'] !== 'admin') {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Area"');
        die('<h1>401 Требуется авторизация</h1>');
    }
}

$pdo = getDB();
$message = '';
$error = '';

if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Анкета #$id удалена.";
    } else {
        $error = "Ошибка удаления.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId = (int)$_POST['edit_id'];
    $data = [
        'fullname'       => $_POST['fullname'] ?? '',
        'phone'          => $_POST['phone'] ?? '',
        'email'          => $_POST['email'] ?? '',
        'birthdate'      => $_POST['birthdate'] ?? '',
        'gender'         => $_POST['gender'] ?? 'unspecified',
        'fav_langs'      => $_POST['fav_langs'] ?? [],
        'bio'            => $_POST['bio'] ?? '',
        'contract_agreed'=> $_POST['contract_agreed'] ?? ''
    ];
    // Здесь можно вызвать validateApplication, но для упрощения пропустим
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE applications SET fullname=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract_agreed=1 WHERE id=?");
        $stmt->execute([$data['fullname'], $data['phone'] ?: null, $data['email'], $data['birthdate'] ?: null, $data['gender'], $data['bio'] ?: null, $editId]);
        $stmtDel = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmtDel->execute([$editId]);
        $stmtLangId = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
        $stmtIns = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($data['fav_langs'] as $lang) {
            $stmtLangId->execute([$lang]);
            $langId = $stmtLangId->fetchColumn();
            if ($langId) $stmtIns->execute([$editId, $langId]);
        }
        $pdo->commit();
        $message = "Анкета #$editId обновлена.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Ошибка обновления: " . $e->getMessage();
    }
}

$editMode = false;
$editData = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editData) {
        $editMode = true;
        $stmtLang = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?");
        $stmtLang->execute([$editId]);
        $editLangNames = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
        $editData['fav_langs'] = $editLangNames;
    } else {
        $error = "Анкета не найдена.";
    }
}

authenticateAdmin();

$applications = $pdo->query("SELECT * FROM applications ORDER BY id DESC")->fetchAll();
$stats = $pdo->query("
    SELECT pl.name, COUNT(al.application_id) as cnt 
    FROM programming_languages pl
    LEFT JOIN application_languages al ON pl.id = al.language_id
    GROUP BY pl.id
    ORDER BY cnt DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 20px; }
        .container { max-width: 1400px; margin: auto; background: white; padding: 20px; border-radius: 16px; }
        h1, h2 { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #2dd4bf; color: #0f172a; }
        tr:nth-child(even) { background: #f9fafb; }
        .btn { display: inline-block; padding: 6px 12px; margin: 2px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .form-edit { background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .form-group { margin-bottom: 10px; }
        label { display: inline-block; width: 180px; font-weight: bold; }
        input, select, textarea { padding: 5px; width: 300px; border-radius: 6px; border: 1px solid #ccc; }
        .stats { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .stat-card { background: #e0f2fe; padding: 8px 12px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Панель администратора</h1>
    <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($editMode && $editData): ?>
        <div class="form-edit">
            <h2>Редактирование анкеты #<?= $editId ?></h2>
            <form method="post">
                <input type="hidden" name="edit_id" value="<?= $editId ?>">
                <div class="form-group"><label>ФИО *:</label> <input type="text" name="fullname" value="<?= htmlspecialchars($editData['fullname'] ?? '') ?>" required></div>
                <div class="form-group"><label>Телефон:</label> <input type="text" name="phone" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>"></div>
                <div class="form-group"><label>E-mail *:</label> <input type="email" name="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" required></div>
                <div class="form-group"><label>Дата рождения:</label> <input type="date" name="birthdate" value="<?= htmlspecialchars($editData['birthdate'] ?? '') ?>"></div>
                <div class="form-group"><label>Пол:</label> <select name="gender"><option value="male" <?= ($editData['gender']??'')=='male'?'selected':'' ?>>Мужской</option><option value="female" <?= ($editData['gender']??'')=='female'?'selected':'' ?>>Женский</option><option value="other" <?= ($editData['gender']??'')=='other'?'selected':'' ?>>Другой</option><option value="unspecified" <?= ($editData['gender']??'')=='unspecified'?'selected':'' ?>>Не указан</option></select></div>
                <div class="form-group"><label>Языки *:</label> <select name="fav_langs[]" multiple><?php $allLangs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go']; foreach($allLangs as $lang){ $selected = in_array($lang, $editData['fav_langs']??[])?'selected':''; echo "<option value=\"$lang\" $selected>$lang</option>"; } ?></select></div>
                <div class="form-group"><label>Биография:</label> <textarea name="bio" rows="3"><?= htmlspecialchars($editData['biography'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Согласие:</label> <input type="checkbox" name="contract_agreed" value="on" <?= ($editData['contract_agreed']??0)?'checked':'' ?>></div>
                <button type="submit">Сохранить</button> <a href="admin.php">Отмена</a>
            </form>
        </div>
    <?php endif; ?>
    <h2>Все анкеты (<?= count($applications) ?>)</h2>
    <table><thead><tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Дата рожд.</th><th>Пол</th><th>Языки</th><th>Биография</th><th>Согласие</th><th>Действия</th></tr></thead><tbody><?php foreach ($applications as $app): $stmtLang = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?"); $stmtLang->execute([$app['id']]); $langs = $stmtLang->fetchAll(PDO::FETCH_COLUMN); $langList = implode(', ', $langs); ?><tr><td><?= $app['id'] ?></td><td><?= htmlspecialchars($app['fullname']) ?></td><td><?= htmlspecialchars($app['phone']) ?></td><td><?= htmlspecialchars($app['email']) ?></td><td><?= htmlspecialchars($app['birthdate']) ?></td><td><?= htmlspecialchars($app['gender']) ?></td><td><?= htmlspecialchars($langList) ?></td><td><?= htmlspecialchars(substr($app['biography'] ?? '', 0, 100)) ?>...</td><td><?= $app['contract_agreed'] ? 'Да' : 'Нет' ?></td><td><a href="admin.php?edit=<?= $app['id'] ?>" class="btn btn-edit">Редактировать</a> <a href="admin.php?delete=<?= $app['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить?')">Удалить</a></td></tr><?php endforeach; ?></tbody></table>
    <h2>Статистика по языкам</h2><div class="stats"><?php foreach ($stats as $stat): ?><div class="stat-card"><?= htmlspecialchars($stat['name']) ?>: <?= $stat['cnt'] ?></div><?php endforeach; ?></div>
</div>
</body>
</html>
