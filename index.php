<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskRepository.php';

if (
    !isset($_SESSION['is_logged_in'])
    || $_SESSION['is_logged_in'] !== true
) {
    header('Location: login.php');
    exit;
}

$repo = new TaskRepository(__DIR__ . '/tasks.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '') ?: null;

        if ($title !== '') {
            $repo->add($title, $deadline);
        }
    } elseif ($action === 'complete') {
        $repo->complete((int) ($_POST['id'] ?? 0));
    } elseif ($action === 'delete') {
        $repo->delete((int) ($_POST['id'] ?? 0));
    }

    header('Location: index.php');
    exit;
}

$tasks = $repo->findAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Менеджер завдань</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f4f4f4;
        }

        h1 {
            text-align: center;
        }

        .stats, .task, .add-form {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .stats {
            text-align: center;
        }

        .add-form, .task {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        input, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[name="title"] {
            flex: 1;
        }

        button {
            cursor: pointer;
        }

        .completed {
            color: green;
        }

        .overdue {
            color: red;
        }

        .progress {
            color: #555;
        }

        .actions {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>

<h1>Менеджер завдань</h1>

<div class="stats">
    Всього: <?= count($tasks) ?> |
    Виконано: <?= $repo->countCompleted() ?> |
    Прострочено: <?= $repo->countOverdue() ?>
</div>

<form class="add-form" method="POST">
    <input type="hidden" name="action" value="add">
    <input
        type="text"
        name="title"
        placeholder="Назва завдання"
        required
    >
    <input type="date" name="deadline">
    <button type="submit">Додати</button>
</form>

<?php if (empty($tasks)): ?>
    <p>Завдань поки немає.</p>
<?php endif; ?>

<?php foreach ($tasks as $task): ?>
    <?php
    $status = $task->getStatusLabel();

    $statusClass = match ($status) {
        'Виконано' => 'completed',
        'Прострочено' => 'overdue',
        default => 'progress',
    };
    ?>

    <div class="task">
        <div>
            <strong>
                <?= htmlspecialchars(
                    $task->getTitle(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <p class="<?= $statusClass ?>">
                <?= htmlspecialchars(
                    $status,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <small>
                Дедлайн:
                <?= htmlspecialchars(
                    $task->getDeadline() ?? 'Не вказано',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </small>
        </div>

        <div class="actions">
            <?php if (!$task->isCompleted()): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="complete">
                    <input
                        type="hidden"
                        name="id"
                        value="<?= $task->getId() ?>"
                    >
                    <button type="submit" title="Виконати">✓</button>
                </form>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input
                    type="hidden"
                    name="id"
                    value="<?= $task->getId() ?>"
                >
                <button type="submit" title="Видалити">✕</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<p><a href="logout.php">Вийти</a></p>

</body>
</html>