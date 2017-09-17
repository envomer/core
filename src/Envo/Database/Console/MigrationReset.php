<?php

namespace Envo\Database\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Phinx\Config\Config;

class MigrationReset extends \Phinx\Console\Command\Rollback
{
    protected function configure()
    {
        parent::configure();

        $this->setName('migrate:reset');
    }

    /**
     * Rollback the migration.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $environment = $input->getOption('environment');
        $version     = $input->getOption('target');
        $date        = $input->getOption('date');
        $force       = !!$input->getOption('force');

        $config = $this->getConfig();

        if ($environment === null) {
            $environment = $config->getDefaultEnvironment();
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        $envOptions = $config->getEnvironment($environment);
        if (isset($envOptions['adapter'])) {
            $output->writeln('<info>using adapter</info> ' . $envOptions['adapter']);
        }

        if (isset($envOptions['wrapper'])) {
            $output->writeln('<info>using wrapper</info> ' . $envOptions['wrapper']);
        }

        if (isset($envOptions['name'])) {
            $output->writeln('<info>using database</info> ' . $envOptions['name']);
        }

        $versionOrder = $this->getConfig()->getVersionOrder();
        $output->writeln('<info>ordering by </info>' . $versionOrder . " time");

        $targetMustMatchVersion = false;
        $target = $this->getTargetFromDate('19700101');

        $start = microtime(true);
        $this->getManager()->rollback($environment, $target, $force, $targetMustMatchVersion);
        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }
}