<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

class Phpcs extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpcs:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Check coding style via PHPCS';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('%s/vendor/bin/phpcs --extensions=php --encoding=utf-8 %s', self::$rootDir, implode(' ', $targets)),
        ];

        if (isset($this->getConfig()['ruleset'])) {
            $commandParts[] = sprintf('--standard=%s/%s', self::$rootDir, $this->getConfig()['ruleset']);
        }

        return implode(' ', $commandParts);
    }

    protected function getConfigKey(): string
    {
        return 'phpcs';
    }

}