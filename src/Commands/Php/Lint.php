<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

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
            sprintf('php %s/vendor/bin/parallel-lint --no-progress --show-deprecated --exclude .git --exclude vendor %s ', self::$rootDir, implode(' ', $targets)),
        ];

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setHelp('PHP lint code in your code base');
    }

    protected function getConfigKey(): string
    {
        return 'lint';
    }

}