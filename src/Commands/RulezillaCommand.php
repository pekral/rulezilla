<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use Illuminate\Support\Collection;
use React\Promise\Promise;
use Rulezilla\OutputPrinter;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function collect;
use function exec;
use function in_array;
use function React\Async\parallel;
use function sprintf;

abstract class RulezillaCommand extends Command
{

    private const STATUS_CODE_MAX_LIMIT_VALUE = 2;

    protected bool $stopOnFailure = true;
    protected Timer $timer;
    protected static bool $parallel = false;
    protected static bool $debug = false;
    protected static string $rootDir;
    protected static bool $isFastCheck = false;

    /**
     * @var array<\Rulezilla\Commands\RulezillaCommand>
     */
    private array $allCommands;

    private static bool $isFixer = false;

    abstract protected function getProcessCommand(): string;

    abstract protected function getConfigKey(): string;

    public function __construct(private array $config, private array $fixers = [], private array $checkers = [])
    {
        parent::__construct();

        $this->allCommands = array_merge($fixers, $checkers);
        self::$rootDir = $config['rulezilla']['rootDir'];
        self::$isFixer = $this instanceof Fixer;
        $this->timer = new Timer();
        $this->stopOnFailure = isset($this->config['stopOnFailure']) && (bool) $this->config['stopOnFailure'] === true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $this->initProperties($input);
        OutputPrinter::printHeader($output, self::$isFixer, self::$parallel, $this->getConfigKey(), self::$isFastCheck);
        $this->timer->start();
        exec($this->getProcessCommand(), $cliOutput, $resultCode);
        $isFixerOK = self::$isFixer && $resultCode < self::STATUS_CODE_MAX_LIMIT_VALUE;
        $statusCode = $isFixerOK || $resultCode < self::STATUS_CODE_MAX_LIMIT_VALUE ? Command::SUCCESS : $resultCode;
        OutputPrinter::printResult(
            $output,
            $resultCode,
            $cliOutput,
            self::$debug,
            $this->timer->stop(),
            self::$isFixer,
            self::$parallel,
            $this->getProcessCommand(),
            $this->stopOnFailure,
            $statusCode === Command::SUCCESS,
        );

        return $statusCode;
    }

    protected function getConfig(): array
    {
        /** @var array|null $config */
        $config = $this->config['commands'][$this->getConfigKey()] ?? [];

        return $config ?? [];
    }

    /**
     * @return array
     */
    protected function getTargets(): array
    {
        if (self::$isFastCheck && !in_array($this->getConfigKey(), ['phpunit', 'phpstan'], true)) {
            exec('git diff --name-only --diff-filter=d origin/master | grep -F .php', $diffFiles);

            $targets = collect($diffFiles);
        } else {
            $commandConfig = $this->getConfig();
            $targets = count($commandConfig) === 0 ? collect() : collect($this->getConfig()['directories']);
        }

        return $targets->map(static fn (string $targetPath): string => sprintf('%s/%s', self::$rootDir, $targetPath))->toArray();
    }

    protected function configure(): void
    {
        $this->addOption('debug', null, InputOption::VALUE_NONE, 'Show debug information');
        $this->addOption('fast', null, InputOption::VALUE_NONE, 'Check only changed files');
        $this->addOption('parallel', null, InputOption::VALUE_NONE, 'Run commands parallel');
    }

    private function initProperties(InputInterface $input): void
    {
        self::$isFastCheck = (bool) $input->getOption('fast');
        self::$parallel = (bool) $input->getOption('parallel');
        self::$debug = (bool) $input->getOption('debug');
    }

    private function executeFixers(InputInterface $input, OutputInterface $output): void
    {
        foreach ($this->fixers as $fixer) {
            $fixer->execute($input, $output);
        }
    }

    // @codingStandardsIgnoreStart
    protected function executeParallel(InputInterface $input, OutputInterface $output): int
    {
        $finalExitCode = Command::SUCCESS;
        $output = new SymfonyStyle($input, $output);
        $this->executeFixers($input, $output);

        parallel([
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[0]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[1]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[2]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[3]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[4]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[5]->execute($input, $output));
            }),
            fn () => new Promise(function ($resolve) use ($input, $output) {
                $resolve($this->checkers[6]->execute($input, $output));
            }),
        ])->then(function (array $exitCodes) use (&$finalExitCode) {
            $finalExitCode = $this->getFinalExitCode(collect($exitCodes));
        }, static function (Throwable $e) use ($output) {
            $output->getErrorStyle()->error($e->getMessage());
        });

        if ($finalExitCode === Command::SUCCESS) {
            $output->success(OutputPrinter::SUCCESS_MESSAGE);
        } else {
            $output->getErrorStyle()->error(OutputPrinter::ERROR_MESSAGE);
        }

        return $finalExitCode;
    }
    // @codingStandardsIgnoreEnd

    protected function executeWithoutStopOnFailure(InputInterface $input, OutputInterface $output): int
    {
        return $this->getFinalExitCode(collect($this->allCommands)->map(static fn (RulezillaCommand $command): int => $command->execute($input, $output)));
    }

    protected function executeWithStopOnFailure(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->allCommands as $command) {
            if ($command->execute($input, $output) !== Command::SUCCESS) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    private function getFinalExitCode(Collection $exitCodes): int
    {
        return $exitCodes->filter(static fn (int $statusCode): bool => $statusCode === Command::FAILURE)
            ->count() > 0 ? Command::FAILURE : Command::SUCCESS;
    }

}