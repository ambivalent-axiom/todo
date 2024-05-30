<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Todo;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();
$playCommand = new class extends Command {
    protected static $defaultName = 'start';
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if( ! file_exists('saved/list.json')) {
            $todo = new Todo(Todo::validateName('todo', 'Name Your List: '), $output, $input);
        } else {
            $todo = Todo::load('saved/list.json', $output, $input);
        }
        return Command::SUCCESS;
    }
};
$application->add($playCommand);
$application->setDefaultCommand('start', true);
$application->run();