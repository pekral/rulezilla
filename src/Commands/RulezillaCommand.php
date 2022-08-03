<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function mb_strtolower;
use function ucfirst;

use const PHP_EOL;

abstract class RulezillaCommand extends Command
{

    protected static string $rootDir;

    abstract protected function getProcessCommand(): string;

    abstract protected function getConfigKey(): string;

    public function __construct(private array $config)
    {
        parent::__construct();

        self::$rootDir = $config['rulezilla']['rootDir'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        exec($this->getProcessCommand(), $cliOutput, $resultCode);

        assert($input->isInteractive());

        if ($resultCode !== Command::SUCCESS) {
            $output->writeln(implode(PHP_EOL, $cliOutput));
        } elseif ($this instanceof Fixer) {
            $output->writeln(sprintf('<info>%s | fix</info>', ucfirst(mb_strtolower($this->getConfigKey()))));
            $output->writeln(implode(PHP_EOL, $cliOutput));
        } else {
            $output->writeln(sprintf('<info>✅ %s</info>', ucfirst(mb_strtolower($this->getConfigKey()))));
        }

        return $resultCode;
    }

    protected function getConfig(): array
    {
        return $this->config['commands'][$this->getConfigKey()];
    }

    /**
     * @return array<string>
     */
    protected function getTargets(): array
    {
        return array_map(
            static fn (string $targetPath) => sprintf('%s/%s', self::$rootDir, $targetPath), $this->getConfig()['directories'],
        );
    }

}