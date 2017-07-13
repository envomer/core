<?php

namespace Envo\Console\Command;

use Envo\Support\File;

use Phinx\Console\Command\Migrate;
use Phinx\Util\Util;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Scaffold extends Migrate
{
    protected function configure()
    {
        parent::configure();

        $this->setName('migration:scaffold');
        $this->setDescription('Scaffold database migrations such as user, events,...'); 
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $manager = $this->getManager();

        // Ask the user which of their defined paths they'd like to use:
        $answer = $this->askWhichMigration($input, $output);
        if( $answer === 'none' ) {
            return true;
        }
        $files = $this->getFiles($answer);
        $versions = $this->prepareFiles($files, $input, $output);
        $manager->setMigrations($versions);

        parent::execute($input, $output);
    }

    public function prepareFiles($files, $input, $output)
    {
        foreach($files as $filePath) {
            $version = Util::getVersionFromFileName(basename($filePath));
            $class = Util::mapFileNameToClassName(basename($filePath));
            $fileNames[$class] = basename($filePath);
            require_once $filePath;
            $migration = new $class($version, $input, $output);
            $versions[$version] = $migration;
        }
        ksort($versions);
        return $versions;
    }

    public function askWhichMigration($input, $output)
    {
        $paths = [
            'none',
            'user',
            'event',
            'client',
        ];

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Which migrations would you like to migrate?', $paths, 0);

        return $helper->ask($input, $output, $question);
    }

    public function getFiles($group)
    {
        $files = [
            'user' => [
                ENVO_PATH . '../../migrations/20170712093749_create_users.php'
            ],
            'event' => [
                ENVO_PATH . '../../migrations/20170712182747_create_clients.php'
            ],
            'client' => [
                ENVO_PATH . '../../migrations/20170713083404_create_events.php',
                ENVO_PATH . '../../migrations/20170713084109_create_event_types.php',
                ENVO_PATH . '../../migrations/20170713084113_create_event_models.php'
            ],
        ];

        return $files[$group];
    }
}