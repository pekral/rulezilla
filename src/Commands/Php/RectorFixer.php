<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\Fixer;
use Rulezilla\Commands\RulezillaCommand;

final class RectorFixer extends RulezillaCommand implements Fixer
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'rector:fix';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Fix code base via Rector';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/rector process %s --no-progress-bar', self::$rootDir, implode(' ', $targets)),
        ];

        if (isset($this->getConfig()['config'])) {
            $commandParts[] = sprintf('-c %s/%s', self::$rootDir, $this->getConfig()['config']);
        }

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setHelp('This command run Rector fixer');
    }

    protected function getConfigKey(): string
    {
        return 'rector';
    }

}