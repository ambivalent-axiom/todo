<?php
namespace App;
use Carbon\Carbon;
use JsonSerializable;

class Task implements JsonSerializable
{
    private int $id;
    private string $name;
    private Todo $todo;
    private string $state;
    private Carbon $stateStart;
    private Carbon $stateEnd;


    public function __construct(
        int $id,
        string $name,
        string $state,
        Carbon $stateStart,
        Carbon $stateEnd
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->state = $state;
        $this->stateStart = $stateStart;
        $this->stateEnd = $stateEnd;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->state,
            'created' => $this->stateStart,
            'deadline' => $this->stateEnd,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getState(): string
    {
        return $this->state;
    }
    public function setState(string $state): void
    {
        $this->state = $state;
    }
    public function setStateStart(Carbon $stateStart): void
    {
        $this->stateStart = $stateStart;
    }
    public function getStateStart(): Carbon
    {
        return $this->stateStart;
    }
    public function setTodo(Todo $todo): void
    {
        $this->todo = $todo;
    }
}