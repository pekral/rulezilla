<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Rulezilla\OutputPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DefaultCommand extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Run commands';

    /**
     * @param array $config
     * @param array<\Symfony\Component\Console\Command\Command> $fixers
     * @param array<\Symfony\Component\Console\Command\Command> $checkers
     */
    public function __construct(array $config, array $fixers, array $checkers)
    {
        parent::__construct($config, $fixers, $checkers);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $parallel = (bool) $input->getOption('parallel');

        if ($parallel) {
            $this->timer->start();
            $result = $this->executeParallel($input, $output);
            $output->info(OutputPrinter::getDurationString($this->timer->stop()));

            return $result;
        }

        return $this->stopOnFailure
            ? $this->executeWithStopOnFailure($input, $output)
            : $this->executeWithoutStopOnFailure($input, $output);
    }

    protected function getProcessCommand(): string
    {
        return '';
    }

    protected function getConfigKey(): string
    {
        return 'rulezilla';
    }

}