<?php

declare(strict_types = 1);

namespace Rulezilla\Commands\Php;

use Rulezilla\Commands\RulezillaCommand;

use function is_string;

final class Phpunit extends RulezillaCommand
{

    private const DEFAULT_COVERAGE_FOLDER = 'storage/code-coverage';

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
        $config = $this->getConfig();
        $isCoverageEnabled = isset($config['coverage']) && $config['coverage'] === true;
        $commandParts = [];

        if ($isCoverageEnabled) {
            $commandParts = $this->createCoverageCommandParts();
        } else {
            $commandParts[] = $this->createBasePhpunitCommand();
            $commandParts[] = '--no-coverage';
        }

        if (isset($config['config'])) {
            $commandParts[] = sprintf('-c %s/%s', self::$rootDir, $config['config']);
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

    private function createCoverageCommandParts(): array
    {
        $config = $this->getConfig();
        $coverageFolder = isset($config['coverage_folder']) && is_string($config['coverage_folder'])
            ? $config['coverage_folder']
            : self::DEFAULT_COVERAGE_FOLDER;

        return [
            'ENV XDEBUG_MODE=coverage',
            $this->createBasePhpunitCommand(),
            sprintf('--coverage-html %s', $coverageFolder),
        ];
    }

    private function createBasePhpunitCommand(): string
    {
        $targets = $this->getTargets();

        return sprintf(
            'php %s/vendor/bin/phpunit %s --dont-report-useless-tests --strict-coverage --default-time-limit 0.75 --enforce-time-limit --colors auto',
            self::$rootDir,
            implode(' ', $targets),
        );
    }

}