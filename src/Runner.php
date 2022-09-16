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
use Rulezilla\Exceptions\InvalidConfig;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

use function array_merge;
use function is_array;
use function sprintf;

final class Runner
{

    private const CONFIG_DEFAULT_FILE = 'rulezilla.yaml';

    private array $config;

    private Application $console;

    /**
     * @throws \Rulezilla\Exceptions\InvalidConfig
     */
    public function __construct(private string $rootDir)
    {
        $this->initConfig();
        $this->console = new Application();
    }

    /**
     * @throws \Exception
     */
    public function run(): int
    {
        $commands = array_merge($this->getFixersCommands(), $this->getCheckersCommands());
        $this->console->addCommands($commands);
        $this->console->add(new DefaultCommand($this->config, $commands));
        $this->console->setDefaultCommand(DefaultCommand::class);

        return $this->console->run();
    }

    /**
     * @throws \Rulezilla\Exceptions\InvalidConfig
     */
    private function initConfig(): void
    {
        $parsedConfig = Yaml::parseFile(self::CONFIG_DEFAULT_FILE);

        if (!is_array($parsedConfig)) {
            throw new InvalidConfig(sprintf('Invalid config file content in %s', self::CONFIG_DEFAULT_FILE));
        }

        $parsedConfig['rulezilla']['rootDir'] = $this->rootDir;

        $this->config = $parsedConfig;
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