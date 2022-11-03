<?php

declare(strict_types = 1);

namespace Rulezilla\Config;

use Rulezilla\Exceptions\InvalidConfig;
use Symfony\Component\Yaml\Yaml;

use function copy;
use function dirname;
use function is_file;
use function sprintf;

final class ConfigFacade
{

    private const CONFIG_DEFAULT_FILE = 'rulezilla.yaml';

    public function __construct(private string $rootDir)
    {

    }

    /**
     * @throws \Rulezilla\Exceptions\InvalidConfig
     */
    public function parseConfig(): array
    {
        $this->createConfigFile();
        $parsedConfig = Yaml::parseFile(self::CONFIG_DEFAULT_FILE);

        if (!is_array($parsedConfig)) {
            throw new InvalidConfig(sprintf('Invalid config file content in %s', self::CONFIG_DEFAULT_FILE));
        }

        $parsedConfig['rulezilla']['rootDir'] = $this->rootDir;

        return $parsedConfig;
    }

    private function createConfigFile(): void
    {
        $configFile = sprintf('%s/%s', $this->rootDir, self::CONFIG_DEFAULT_FILE);
        $distConfigFile = dirname(__DIR__) . '/../dist/rulezilla.yaml.dist';

        if (is_file($configFile) || !copy($distConfigFile, $configFile)) {
            return;
        }

        echo 'Config file created.';
    }

}