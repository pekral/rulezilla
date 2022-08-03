<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

use function implode;
use function sprintf;

final class Rector extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'rector:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Refactoring code base via Rector';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/rector process %s --dry-run --no-progress-bar', self::$rootDir, implode(' ', $targets)),
        ];

        if (isset($this->getConfig()['config'])) {
            $commandParts[] = sprintf('-c %s/%s', self::$rootDir, $this->getConfig()['config']);
        }

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        $this->setHelp('This command run Rector refactoring');
    }

    protected function getConfigKey(): string
    {
        return 'rector';
    }

}