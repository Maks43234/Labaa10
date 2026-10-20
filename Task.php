<?php
declare(strict_types=1);

class Task
{
    public function __construct(
        public readonly int $id,
        private string $title,
        private bool $isCompleted = false,
        private ?string $deadline = null
    ) {
        if (trim($title) === '') {
            throw new InvalidArgumentException(
                'Назва завдання не може бути порожньою.'
            );
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted || $this->deadline === null) {
            return false;
        }

        $time = strtotime($this->deadline);

        return $time !== false && $time < time();
    }

    public function complete(): void
    {
        $this->isCompleted = true;
    }

    public function getStatusLabel(): string
    {
        if ($this->isCompleted) {
            return 'Виконано';
        }

        if ($this->isOverdue()) {
            return 'Прострочено';
        }

        return 'В процесі';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isCompleted' => $this->isCompleted,
            'deadline' => $this->deadline,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            isCompleted: (bool) ($data['isCompleted'] ?? false),
            deadline: $data['deadline'] ?? null
        );
    }
}