<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function in_array;

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
     * @param array<\Rulezilla\Commands\RulezillaCommand> $commands
     */
    public function __construct(array $config, private array $commands)
    {
        parent::__construct($config);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resultCodes = [];

        foreach ($this->commands as $command)
        {
            $resultCodes[] = $command->execute($input, $output);
        }

        return in_array(Command::FAILURE, $resultCodes, true) ? Command::FAILURE : Command::SUCCESS;
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