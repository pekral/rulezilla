<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

final class Phpcpd extends RulezillaCommand
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'phpcpd:run';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Check copy paste code in code base';

    protected function getProcessCommand(): string
    {
        $targets = $this->getTargets();

        $commandParts = [
            sprintf('php %s/vendor/bin/phpcpd %s ', self::$rootDir, implode(' ', $targets)),
        ];

        return implode(' ', $commandParts);
    }

    protected function configure(): void
    {
        $this->setHelp('This command check duplicated code in your code base');
    }

    protected function getConfigKey(): string
    {
        return 'phpcpd';
    }

}