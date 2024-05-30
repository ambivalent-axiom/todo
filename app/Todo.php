<?php
namespace App;
use Carbon\Carbon;
use JsonSerializable;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Todo implements JsonSerializable
{
    private string $name;
    private array $tasks;
    private OutputInterface $symfonyOutput;
    private InputInterface $symfonyInput;
    private QuestionHelper $helper;

    const VALID_STR_LENGTH = 12; //string validation function

    public function __construct(
        string          $name,
        OutputInterface $symfonyOutput,
        InputInterface  $symfonyInput,
        array           $tasks=[])
    {
            $this->name = $name;
            $this->tasks = $tasks;
            $this->symfonyOutput = $symfonyOutput;
            $this->symfonyInput = $symfonyInput;
            $this->helper = new QuestionHelper();
            $this->initTasksOnLoad();
            $this->mainLoop();
    }
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'tasks' => $this->tasks,
        ];
    }
    private function show(): void
    {
        $table = new Table($this->symfonyOutput);
        $table
            ->setHeaders(['id', 'task name', 'status'])
            ->setRows(array_map(function ($task) {
                return [
                    $task->getId(),
                    $task->getName(),
                    $task->getState(),
                ];
            }, $this->tasks));
        $table->setHeaderTitle($this->name);
        $table->setStyle('box-double');
        $table->render();
    }
    private function addTask(int $id, string $name, string $state, Carbon $stateEnd): void
    {
        $newTask = new Task($id, $name, $state, $stateEnd);
        $newTask->setTodo($this);
        $this->tasks[] = $newTask;
    }
    private function saveZoo(): void
    {
        $todo = 'saved/list.json';
        $json = json_encode($this);
        file_put_contents($todo, $json);
    }
    private function mainLoop(): void
    {
        $options = [
            'add',
            'edit',
            'remove',
            'exit'
        ];
        while(true) {
            $this->cls();
            $this->show();
            $choice = new ChoiceQuestion('What would you like to do?', $options);
            $choice->setErrorMessage('Option %s is invalid.');
            $choice = $this->helper->ask($this->symfonyInput, $this->symfonyOutput, $choice);
            switch ($choice)
            {
                case 'add':
                    echo "add";
                    break;
                case 'edit':
                    echo "edit";
                    break;
                case 'remove':
                    if($this->checkTaskCount()) {
                        break;
                    }
                    $task = $this->selectTasks();
                    $this->removeTask($task);
                    break;
                case 'exit':
                    exit;
            }
        }
    }
    private function initTasksOnLoad(): void
    {
        foreach ($this->tasks as $animal) {
            $animal->setUser($this->user);
            $animal->setTodo($this);
        }
    }
    private function selectTasks(): Task
    {   $this->cls();
        $this->show();
        $options = array_map(function ($task) {
            return strtolower($task->getName());
        }, $this->tasks);
        $choice = new ChoiceQuestion('Choose a task to interact?', $options);
        $choice->setErrorMessage('Option %s is invalid.');
        $choice = $this->helper->ask($this->symfonyInput, $this->symfonyOutput, $choice);
        $index = array_search($choice, $options);
        return $this->tasks[$index];
    }
    private function removeTask($task): void
    {
        $index = array_search($task, $this->tasks);
        unset($this->tasks[$index]);
    }
    public function getName(): string
    {
        return $this->name;
    }
    private function checkTaskCount(): bool {
        if(count($this->tasks) === 0) {
            echo 'No tasks!';
            return true;
        }
        return false;
    }
    public static function cls(): void {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            system('cls');
        } else {
            system('clear');
        }
    }
    public static function validateName(string $who, string $prompt): string
    {
        while(true) {
            $name = readline($prompt);
            if($name != '' && strlen($name) <= self::VALID_STR_LENGTH && !is_numeric($name)) {
                return $name;
            }
            echo "$who name must be a string, max 12 chars.\n";
        }
    }
    public static function load(string $json, OutputInterface $output, InputInterface $input): Todo
    {
        $todo = json_decode(file_get_contents($json));
        $tasks = [];

        foreach ($todo->tasks as $task) {
            $tasks[] = new Task(
                $task->id,
                $task->name,
                $task->state,
                $task->created,
                $task->deadline
            );
        }
        return new self($todo->name, $output, $input, $tasks);
    }
}