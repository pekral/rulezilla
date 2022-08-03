<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

final class Phpunit extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpunit:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Check PHP tests';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf(
                'php %s/vendor/bin/phpunit %s --dont-report-useless-tests --strict-coverage --default-time-limit 0.75 --enforce-time-limit --colors auto',
                self::$rootDir,
                implode(' ', $targets),
            ),
        ];

        if (isset($this->getConfig()['config'])) {
            $commandParts[] = sprintf('-c %s/%s', self::$rootDir, $this->getConfig()['config']);
        }

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        $this->setHelp('Check PHP tests');
    }

    protected function getConfigKey(): string
    {
        return 'phpunit';
    }

}