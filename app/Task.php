<?php
namespace App;
use JsonSerializable;

class Task implements JsonSerializable
{
    private int $id;
    private string $name;
    private Todo $todo;
    private string $state;
    private string $stateStart;
    private string $deadline;
    private $states = ['ToDo', 'In Progress', 'Completed'];


    public function __construct(
        int $id,
        string $name,
        string $stateStart,
        string $stateEnd,
        string $state = 'ToDo'
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->state = $state;
        $this->stateStart = $stateStart;
        $this->deadline = $stateEnd;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->state,
            'created' => $this->stateStart,
            'deadline' => $this->deadline,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getState(): string
    {
        return $this->state;
    }
    public function setState(string $state): void
    {
        $this->state = $state;
    }
    public function setStateStart(string $stateStart): void
    {
        $this->stateStart = $stateStart;
    }
    public function getStateStart(): string
    {
        return $this->stateStart;
    }
    public function getDeadline(): string
    {
        return $this->deadline;
    }
    public function setStateEnd(string $deadline): void
    {
        $this->deadline = $deadline;
    }
    public function setTodo(Todo $todo): void
    {
        $this->todo = $todo;
    }
}