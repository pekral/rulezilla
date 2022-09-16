<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    public function __construct(private array $config, array $fixers, array $checkers)
    {
        parent::__construct($config, $fixers, $checkers);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        if ($this->parallel) {
            return $this->executeParallel($input, $output);
        }

        $stopOnFailure = isset($this->config['stopOnFailure']) && (bool) $this->config['stopOnFailure'] === true;

        return $stopOnFailure
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