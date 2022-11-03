<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function exec;
use function implode;

final class InitCommand extends Command
{

    /**
     * @var string|null
     */
    protected static $defaultName = 'init';

    /**
     * @var string|null
     */
    protected static $defaultDescription = 'Init Rulezilla tools';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $input->validate();

        $commands = [
            'composer require --dev rector/rector',
            'composer require --dev phpstan/phpstan',
            'composer require --dev php-parallel-lint/php-parallel-lint',
            'composer require --dev phpstan/phpstan-phpunit',
            'composer require --dev phpunit/phpunit',
            'composer require --dev phpstan/extension-installer',
            'composer require --dev slevomat/coding-standard',
            'composer require --dev povils/phpmnd',
            'composer require --dev sebastian/phpcpd',
            'composer require --dev php-parallel-lint/php-console-highlighter',
            'vendor/bin/rector init',
        ];
        exec(implode(' && ', $commands), $output, $resultCode);

        return $resultCode;
    }

}