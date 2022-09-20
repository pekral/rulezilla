<?php

declare(strict_types = 1);

namespace Rulezilla;

use Rulezilla\Commands\DefaultCommand;
use Rulezilla\Commands\Php\Lint;
use Rulezilla\Commands\Php\Phpcpd;
use Rulezilla\Commands\Php\Phpcs;
use Rulezilla\Commands\Php\PhpcsFixer;
use Rulezilla\Commands\Php\Phpmnd;
use Rulezilla\Commands\Php\Phpstan;
use Rulezilla\Commands\Php\Phpunit;
use Rulezilla\Commands\Php\Rector;
use Rulezilla\Commands\Php\RectorFixer;
use Rulezilla\Config\ConfigFacade;
use Symfony\Component\Console\Application;

use function array_merge;

final class Runner
{

    private array $config;

    private Application $console;

    /**
     * @throws \Rulezilla\Exceptions\InvalidConfig
     */
    public function __construct(string $rootDir)
    {
        $this->console = new Application();
        $this->config = (new ConfigFacade($rootDir))->parseConfig();
    }

    /**
     * @throws \Exception
     */
    public function run(): int
    {
        $this->console->addCommands(
            array_merge(
                $this->getFixersCommands(),
                $this->getCheckersCommands(),
                [new DefaultCommand($this->config, $this->getFixersCommands(), $this->getCheckersCommands())],
            ),
        );
        $this->console->setDefaultCommand(DefaultCommand::class);

        return $this->console->run();
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    private function getCheckersCommands(): array
    {
        return [
            new Lint($this->config),
            new Phpcpd($this->config),
            new Phpmnd($this->config),
            new Phpcs($this->config),
            new Rector($this->config),
            new Phpstan($this->config),
            new Phpunit($this->config),
        ];
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    private function getFixersCommands(): array
    {
        return [
            new RectorFixer($this->config),
            new PhpcsFixer($this->config),
        ];
    }

}