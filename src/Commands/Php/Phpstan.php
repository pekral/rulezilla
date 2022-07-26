<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

use function implode;
use function sprintf;

final class Phpstan extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpstan:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Analyse code base via PHPStan';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/phpstan analyse %s --no-progress --xdebug', self::$rootDir, implode(' ', $targets)),
        ];

        if (isset($this->getConfig()['config'])) {
            $commandParts[] = sprintf('-c %s/%s', self::$rootDir, $this->getConfig()['config']);
        }

        if (isset($this->getConfig()['memoryLimit'])) {
            $commandParts[] = sprintf('--memory-limit %s', $this->getConfig()['memoryLimit']);
        }

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setHelp('This command run PHPStan analyse');
    }

    protected function getConfigKey(): string
    {
        return 'phpstan';
    }

}