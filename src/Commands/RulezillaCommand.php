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
use Throwable;

use function collect;
use function React\Async\parallel;
use function sprintf;

abstract class RulezillaCommand extends Command
{

    protected bool $parallel;

    protected static string $rootDir;

    /**
     * @var array<\Symfony\Component\Console\Command\Command>
     */
    private array $allCommands = [];

    private Timer $timer;

    abstract protected function getProcessCommand(): string;

    abstract protected function getConfigKey(): string;

    public function __construct(private array $config, private array $fixers = [], private array $checkers = [])
    {
        parent::__construct();

        $this->allCommands = array_merge($fixers, $checkers);
        self::$rootDir = $config['rulezilla']['rootDir'];
        $this->parallel = isset($this->config['parallel']) && (bool) $this->config['parallel'] === true;
        $this->timer = new Timer();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isFixer = $this instanceof Fixer;

        OutputPrinter::printHeader($output, $isFixer, $this->parallel, $this->getConfigKey());
        $this->timer->start();
        exec($this->getProcessCommand(), $cliOutput, $resultCode);
        OutputPrinter::printResult($output, $resultCode, $cliOutput, $input->getOption('debug') !== null, $this->timer->stop(), $isFixer, $this->parallel, $this->getProcessCommand());

        return $isFixer ? Command::SUCCESS : $resultCode;
    }

    protected function getConfig(): array
    {
        return $this->config['commands'][$this->getConfigKey()];
    }

    /**
     * @return array<string>
     */
    protected function getTargets(): array
    {
        return array_map(
            static fn (string $targetPath) => sprintf('%s/%s', self::$rootDir, $targetPath), $this->getConfig()['directories'],
        );
    }

    protected function configure(): void
    {
        $this->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'Show debug info');
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
            $output->writeln($e->getMessage());
        });

        if ($finalExitCode === Command::SUCCESS) {
            $output->writeln(OutputPrinter::SUCCESS_MESSAGE);
        } else {
            $output->writeln(OutputPrinter::ERROR_MESSAGE);
        }

        return $finalExitCode;
    }
    // @codingStandardsIgnoreEnd

    protected function executeWithoutStopOnFailure(InputInterface $input, OutputInterface $output): int
    {
        return $this->getFinalExitCode(collect($this->allCommands)->map(static fn (Command $command): int => $command->execute($input, $output)));
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