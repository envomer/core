<?php

namespace Envo\Console;

use Envo\Database\Migration\Manager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
	
		/** @var array $arguments */
        $arguments = [];
		/** @var array $options */
        $options = [];
        $name = null;
        
        if ( null !== $this->signature){
			list($name, $arguments, $options) = Parser::parse($this->signature);
		}
		
		parent::__construct($this->name = $name);
        
        $definition = $this->getDefinition();
        
        if($arguments || $options) {
			foreach ($arguments as $argument) {
				$definition->addArgument($argument);
			}
			
			foreach ($options as $option) {
				$definition->addOption($option);
			}
		}
		
		if(method_exists($this, 'getOptions')) {
        	$options = $this->getOptions();
			foreach ($options as $option) {
				$definition->addOption(new InputOption(...$option));
			}
		}
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
	
	/**
	 * Call another console command.
	 *
	 * @param  string $command
	 * @param  array $arguments
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function call($command, array $arguments = [])
	{
		$arguments['command'] = $command;
		return $this->getApplication()->find($command)->run(
			$this->createInputFromArguments($arguments), $this->output
		);
	}
	
	/**
	 * Create an input instance from the given arguments.
	 *
	 * @param  array  $arguments
	 * @return \Symfony\Component\Console\Input\ArrayInput
	 */
	protected function createInputFromArguments(array $arguments): \Symfony\Component\Console\Input\ArrayInput
	{
		$input = new ArrayInput($arguments);
		
		if ($input->hasParameterOption(['--no-interaction'], true)) {
			$input->setInteractive(false);
		}
		
		return $input;
	}
}