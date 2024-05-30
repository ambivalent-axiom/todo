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
    const VALID_STR_LENGTH = 30; //string validation function
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
            ->setHeaders(Task::getColumns())
            ->setRows(array_map(function ($task) {
                return [
                    $task->getId(),
                    $task->getCreated(),
                    $task->getName(),
                    $task->getDeadline(),
                    $task->getStatus(),
                ];
            }, $this->tasks));
        $table->setHeaderTitle($this->name);
        $table->setStyle('box-double');
        $table->render();
    }
    private function addTask(int $id, string $name, Carbon $stateStart, Carbon $stateEnd): void
    {
        $newTask = new Task($id, $stateStart->toString(), $name, $stateEnd->toString());
        $this->tasks[] = $newTask;
    }
    private function save(): void
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
                    $this->addTask(
                        $this->getAutoIncrementId(),
                        self::validateName("Task", "Enter task name: "),
                        Carbon::now(),
                        $this->selectDeadline(self::validateNum("Deadline after days?: "))
                    );
                    break;
                case 'edit':
                    if($this->checkTaskCount()) {
                        echo "No tasks to Edit!";
                        break;
                    }
                    $task = $this->selectTasks();
                    $this->editTask($task);
                    break;
                case 'remove':
                    if($this->checkTaskCount()) {
                        echo "No tasks to Remove!";
                        break;
                    }
                    $task = $this->selectTasks();
                    $this->removeTask($task);
                    break;
                case 'exit':
                    $this->save();
                    exit;
            }
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
            return true;
        }
        return false;
    }
    private function getAutoIncrementId(): int
    {
        if (count($this->tasks) === 0) {
            return 0;
        }
        $ids = array_map(function ($task) {
            return $task->getId();
        }, $this->tasks);
        return max($ids) + 1;
    }
    private function selectDeadline(int $days): Carbon
    {
        return Carbon::now()->addDays($days);
    }
    private function editTask(Task $task): void
    {
        $options = array_slice(Task::getColumns(), 2);
        $choice = new ChoiceQuestion('Select property to edit: ', $options);
        $choice->setErrorMessage('Option %s is invalid.');
        $choice = $this->helper->ask($this->symfonyInput, $this->symfonyOutput, $choice);
        switch ($choice) {
            case 'name':
                $task->setName(self::validateName("Task", "Enter new name: "));
                break;
            case 'deadline':
                $created = $task->getCreated();
                echo $deadline = $this->selectDeadline(
                    self::validateNum("Deadline in days from $created?: ")
                );
                $task->setDeadline($deadline->toString());
                break;
            case 'status':
                $task->setStatus($this->selectStatus());
                break;
        }
    }
    private function selectStatus(): string
    {
        $options = Task::getStates();
        $choice = new ChoiceQuestion('Change state to: ', $options);
        $choice->setErrorMessage('Option %s is invalid.');
        return $this->helper->ask($this->symfonyInput, $this->symfonyOutput, $choice);
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
            echo "$who name must be a string, max " . self::VALID_STR_LENGTH . " chars.\n";
        }
    }
    public static function validateNum($prompt): int
    {
        while(true) {
            $num = readline($prompt);
            if (is_numeric($num)) {
                return $num;
            }
            echo "Number must be a valid integer.\n";
        }
    }
    public static function load(string $json, OutputInterface $output, InputInterface $input): Todo
    {
        $todo = json_decode(file_get_contents($json));
        $tasks = [];
        foreach ($todo->tasks as $task) {
            $tasks[] = new Task(
                $task->id,
                $task->created,
                $task->name,
                $task->deadline,
                $task->status,
            );
        }
        return new self($todo->name, $output, $input, $tasks);
    }
}