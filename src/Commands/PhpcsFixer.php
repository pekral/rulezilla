<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

class PhpcsFixer extends RulezillaCommand implements Fixer
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpcs:fix';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Fix coding style via PHPCS';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('%s/vendor/bin/phpcbf --extensions=php --encoding=utf-8 %s', self::$rootDir, implode(' ', $targets)),
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