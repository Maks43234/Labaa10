<?php
declare(strict_types=1);

require_once __DIR__ . '/Task.php';

class TaskRepository
{
    private array $tasks = [];

    public function __construct(private string $storageFile)
    {
        $this->load();
    }

    public function findAll(): array
    {
        return $this->tasks;
    }

    public function findById(int $id): ?Task
    {
        foreach ($this->tasks as $task) {
            if ($task->getId() === $id) {
                return $task;
            }
        }

        return null;
    }

    public function add(string $title, ?string $deadline = null): Task
    {
        $task = new Task(
            $this->generateNextId(),
            $title,
            false,
            $deadline
        );

        $this->tasks[] = $task;
        $this->save();

        return $task;
    }

    public function complete(int $id): bool
    {
        $task = $this->findById($id);

        if ($task === null) {
            return false;
        }

        $task->complete();
        $this->save();

        return true;
    }

    public function delete(int $id): bool
    {
        foreach ($this->tasks as $key => $task) {
            if ($task->getId() === $id) {
                unset($this->tasks[$key]);
                $this->tasks = array_values($this->tasks);
                $this->save();

                return true;
            }
        }

        return false;
    }

    public function countCompleted(): int
    {
        return count(array_filter(
            $this->tasks,
            fn(Task $task) => $task->isCompleted()
        ));
    }

    public function countOverdue(): int
    {
        return count(array_filter(
            $this->tasks,
            fn(Task $task) => $task->isOverdue()
        ));
    }

    private function generateNextId(): int
    {
        if (empty($this->tasks)) {
            return 1;
        }

        return max(array_map(
            fn(Task $task) => $task->getId(),
            $this->tasks
        )) + 1;
    }

    private function load(): void
    {
        if (!file_exists($this->storageFile)) {
            $this->tasks = [];
            return;
        }

        $json = file_get_contents($this->storageFile);

        if ($json === false || trim($json) === '') {
            $this->tasks = [];
            return;
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException('Помилка читання tasks.json.');
        }

        $this->tasks = array_map(
            fn(array $item) => Task::fromArray($item),
            $data
        );
    }

    private function save(): void
    {
        $data = array_map(
            fn(Task $task) => $task->toArray(),
            $this->tasks
        );

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        if ($json === false || file_put_contents(
            $this->storageFile,
            $json,
            LOCK_EX
        ) === false) {
            throw new RuntimeException('Помилка збереження завдань.');
        }
    }
}