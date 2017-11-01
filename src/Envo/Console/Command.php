<?php

namespace Envo\Console;

use Envo\Database\Migration\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Exception\InvalidArgumentException;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
	/**
	 * @var OutputInterface
	 */
    public $output;
	
	/**
	 * @var InputInterface
	 */
    public $input;
	
	/**
	 * @var string
	 */
    protected $description = 'Description missing!!!!';
	
	/**
	 * @var string
	 */
    protected $name;
	
	/**
	 * @var string
	 */
    protected $signature;
	
	/**
	 * @var Manager
	 */
    protected $manager;
	
	/**
	 * Command constructor.
	 *
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 * @throws \Symfony\Component\Console\Exception\LogicException
	 * @throws \InvalidArgumentException
	 */
    public function __construct()
    {
        $this->setName(strtolower(basename(str_replace('\\', '/', static::class))));
        $this->setDescription($this->description);

        if ( null !== $this->signature) {
			/** @var array $arguments */
			/** @var array $options */
			list($name, $arguments, $options) = Parser::parse($this->signature);
	
			parent::__construct($this->name = $name);
	
			// After parsing the signature we will spin through the arguments and options
			// and set them on this command. These will already be changed into proper
			// instances of these "InputArgument" and "InputOption" Symfony classes.
			foreach ($arguments as $argument) {
				$this->getDefinition()->addArgument($argument);
			}
	
			foreach ($options as $option) {
				$this->getDefinition()->addOption($option);
			}
        } else {
            parent::__construct($this->name);
        }
		
		$this->manager = new Manager();
    }
	
	/**
	 * @return mixed
	 */
	abstract public function handle();

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled, $verbosity);
    }
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->handle();
    }
	
	/**
	 * @param      $name
	 * @param null $default
	 *
	 * @return mixed|null
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
    public function option($name, $default = null)
    {
        if( $default !== null ) {
            try {
                return $this->input->getOption($name);
            } catch (InvalidArgumentException $e) {
                return $default;
            }
        }

        return $this->input->getOption($name);
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }
	
    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }
	
    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }
	
    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function warn($string, $verbosity = null)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }
        $this->line($string, 'warning', $verbosity);
    }
	
    /**
     * Write a string in an alert box.
     *
     * @param  string  $string
     * @return void
     */
    public function alert($string)
    {
        $this->comment(str_repeat('*', strlen($string) + 12));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', strlen($string) + 12));
        $this->output->writeln('');
    }
	
	/**
	 * Get the value of a command argument.
	 *
	 * @param  string $key
	 *
	 * @return string|array
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
    public function argument($key = null)
    {
        if ( null === $key ) {
            return $this->input->getArguments();
        }
        return $this->input->getArgument($key);
    }
	
	/**
	 * Get all of the arguments passed to the command.
	 *
	 * @return array
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
    public function arguments()
    {
        return $this->argument();
    }
}