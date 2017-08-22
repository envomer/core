<?php

namespace Envo\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Exception\InvalidArgumentException;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    public $output;
    public $input;
    protected $description = 'Description missing!!!!';

    public function __construct()
    {
        $this->setName(strtolower(basename(str_replace('\\', '/', get_called_class()))));
        $this->setDescription($this->description);

        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }
    }

    /**
     * Configure the console command using a fluent definition.
     *
     * @return void
     */
    protected function configureUsingFluentDefinition()
    {
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
    }

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->handle();
    }

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
     * @param  string  $key
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }
        return $this->input->getArgument($key);
    }
    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    abstract function handle();
}