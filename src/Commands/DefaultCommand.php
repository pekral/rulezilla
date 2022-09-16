<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function collect;

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
     * @param array<\Symfony\Component\Console\Command\Command> $commands
     */
    public function __construct(private array $config, private array $commands)
    {
        parent::__construct($config);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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

    private function executeWithoutStopOnFailure(InputInterface $input, OutputInterface $output): int
    {
        return collect($this->commands)->map(static fn (Command $command): int => $command->execute($input, $output))
            ->filter(static fn (int $statusCode): bool => $statusCode === Command::FAILURE)
            ->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function executeWithStopOnFailure(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->commands as $command) {
            if ($command->execute($input, $output) !== Command::SUCCESS) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

}