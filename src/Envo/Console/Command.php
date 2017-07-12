<?php

namespace Envo\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
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

    public function comment($message)
    {
        $this->output->writeln('<comment>'.$message.'</comment>');
    }

    public function info($message)
    {
        $this->output->writeln('<info>'.$message.'</info>');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->fire();
    }

    public function option($name, $default = null)
    {
        if( $default !== null ) {
            try {
                return $this->input->getArgument($name);
            } catch (InvalidArgumentException $e) {
                return $default;
            }
        }

        return $this->input->getArgument($name);
    }

    abstract function fire();
}