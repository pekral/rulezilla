<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

final class Phpmnd extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpmnd:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Check magic numbers in code base';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/phpmnd %s ', self::$rootDir, implode(' ', $targets)),
        ];

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        $this->setHelp('This command check magic numbers in your code base');
    }

    protected function getConfigKey(): string
    {
        return 'phpmnd';
    }

}