<?php
namespace App;
use JsonSerializable;
class Task implements JsonSerializable
{
    private int $id;
    private string $name;
    private string $status;
    private string $created;
    private string $deadline;
    private const STATES = ['ToDo', 'In Progress', 'Completed'];
    private const PROPERTIES = ['id', 'created', 'name', 'deadline', 'status'];
    public function __construct(
        int $id,
        string $created,
        string $name,
        string $stateEnd,
        string $status = 'ToDo'
    )
    {
        $this->id = $id;
        $this->created = $created;
        $this->name = $name;
        $this->deadline = $stateEnd;
        $this->status = $status;
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created,
            'name' => $this->name,
            'deadline' => $this->deadline,
            'status' => $this->status,
        ];
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function setCreated(string $created): void
    {
        $this->created = $created;
    }
    public function getCreated(): string
    {
        return $this->created;
    }
    public function getDeadline(): string
    {
        return $this->deadline;
    }
    public function setDeadline(string $deadline): void
    {
        $this->deadline = $deadline;
    }
    public static function getColumns(): array
    {
        return self::PROPERTIES;
    }
    public static function getStates(): array
    {
        return self::STATES;
    }
}