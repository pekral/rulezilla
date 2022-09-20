<?php

declare(strict_types = 1);

namespace Rulezilla\Config;

use Rulezilla\Exceptions\InvalidConfig;
use Symfony\Component\Yaml\Yaml;

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
        $parsedConfig = Yaml::parseFile(self::CONFIG_DEFAULT_FILE);

        if (!is_array($parsedConfig)) {
            throw new InvalidConfig(sprintf('Invalid config file content in %s', self::CONFIG_DEFAULT_FILE));
        }

        $parsedConfig['rulezilla']['rootDir'] = $this->rootDir;

        return $parsedConfig;
    }

}