<?php

namespace Envo\Database\Console;

use Envo\Support\Arr;
use Symfony\Component\Console\Question\ChoiceQuestion;

class MigrationScaffold extends BaseCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'migrate:scaffold {--database= : The database connection to use.}
                {--force : Force the operation to run when in production.}
                {--path= : The path of migrations files to be executed.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--migrate= : Define the migration you want to migrate.}
                {--step : Force the migrations to be run so they can be rolled back individually.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scaffold database migrations (user, teams, ...)';
	
	/**
	 * Ask which migration
	 *
	 * @return mixed
	 * @throws \Symfony\Component\Console\Exception\LogicException
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
    public function askWhichMigration()
    {
        $paths = [
            'none',
            'ALL',
            'user',
            'team',
            'event',
            'mail',
            'extension_email_template',
            'permissions',
        ];

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Which migrations would you like to migrate?', $paths, 0);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Get files
     *
     * @param [type] $group
     *
	 * @return array
	 */
    public function getFiles($group)
    {
        $files = [
            'user' => [
                ENVO_PATH . '../migrations/20170712093749_create_users.php'
            ],
            'team' => [
                ENVO_PATH . '../migrations/20170712182747_create_teams.php',
                //ENVO_PATH . '../migrations/20170712093750_create_user_team.php',
            ],
            'event' => [
                ENVO_PATH . '../migrations/20170713083404_create_events.php',
                ENVO_PATH . '../migrations/20170713084109_create_event_types.php',
                ENVO_PATH . '../migrations/20170713084113_create_event_models.php',
                ENVO_PATH . '../migrations/20170713084114_create_ips.php',
            ],
            'mail' => [
                ENVO_PATH . '../migrations/20170713093749_create_mails.php',
            ],
            'permissions' => [
                ENVO_PATH . '../migrations/20170712093753_create_rules.php',
                ENVO_PATH . '../migrations/20170712093754_create_permissions.php',
                ENVO_PATH . '../migrations/20170712093755_create_roles.php',
                ENVO_PATH . '../migrations/20170712093756_create_roles_paths.php',
                ENVO_PATH . '../migrations/20170712093750_create_modules.php',
            ],
			'extension_email_template' => [
				ENVO_PATH . '../migrations/20180106203020_create_extensionEmailTemplate_table.php',
			]
        ];

        if( $group === 'ALL' ) {
            return Arr::flatten($files);
        }
        
        if(!isset($files[$group])) {
        	throw new \Exception('Migration not found');
		}

        return $files[$group] ?? null;
    }
	
	/**
	 * @return mixed
	 * @throws \Symfony\Component\Console\Exception\LogicException
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
	public function handle()
	{
		// Ask the user which of their defined paths they'd like to use:
		$option = $this->option('migrate');
		$answer = $option ?: $this->askWhichMigration();
		if( !$answer || $answer === 'none' ) {
			return true;
		}
		$files = $this->getFiles($answer);
		$this->manager->setMigrationFiles($files);
		
		$this->manager->run();
		
		// Once the manager has run we will grab the note output and send it out to
		// the console screen, since the manager itself functions without having
		// any instances of the OutputInterface contract passed into the class.
		foreach ($this->manager->getNotes() as $note) {
			$this->output->writeln($note);
		}
		
		// Finally, if the "seed" option has been given, we will re-run the database
		// seed task to re-populate the database, which is convenient when adding
		// a migration and a seed at the same time, as it is only this command.
		if ($this->option('seed')) {
			$this->call('db:seed', ['--force' => true]);
		}
	}
}