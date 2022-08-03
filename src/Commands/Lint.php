<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

final class Lint extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'lint:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Check php syntax code in code base';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/parallel-lint --exclude .git --exclude vendor %s ', self::$rootDir, implode(' ', $targets)),
        ];

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        $this->setHelp('PHP lint code in your code base');
    }

    protected function getConfigKey(): string
    {
        return 'lint';
    }

}