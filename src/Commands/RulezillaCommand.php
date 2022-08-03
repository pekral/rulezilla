<?php

declare(strict_types = 1);

namespace Rulezilla\Commands;

use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function mb_strtolower;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

abstract class RulezillaCommand extends Command
{

    protected static string $rootDir;

    private Timer $timer;

    abstract protected function getProcessCommand(): string;

    abstract protected function getConfigKey(): string;

    public function __construct(private array $config)
    {
        parent::__construct();

        self::$rootDir = $config['rulezilla']['rootDir'];
        $this->timer = new Timer();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        assert($input->isInteractive());
        $isFixer = $this instanceof Fixer;
        $isDebugMode = $input->getOption('debug') !== null;

        $this->printHeader($output, $isFixer);
        $this->timer->start();
        exec($this->getProcessCommand(), $cliOutput, $resultCode);
        $this->printResult($output, $resultCode, $cliOutput, $isDebugMode, $this->timer->stop(), $isFixer);

        return $resultCode;
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

    private function printResult(OutputInterface $output, int $resultCode, array $cliOutput, bool $isDebugMode, Duration $duration, bool $isFixer): void
    {
        $isSuccess = $resultCode !== Command::SUCCESS;

        if ($isSuccess && !$isFixer) {
            $output->write('<error>[ERROR]</error>');
            $output->writeln(implode(PHP_EOL, $cliOutput));

        } else {
            $output->write('<info>[OK]</info>', true);
        }

        if (!$isDebugMode && $isSuccess) {
            return;
        }

        $output->writeln('<info>[DEBUG]</info>');
        $output->writeln('[Command] '. $this->getProcessCommand());
        $output->writeln('[Executing time] '. $duration->asString());
    }

    private function printHeader(OutputInterface $output, bool $isFixer): void
    {
        $commandConfigKey = ucfirst(mb_strtolower($this->getConfigKey()));
        $output->write(sprintf('<comment>[%s]</comment>', $commandConfigKey));

        $processInfo = $isFixer ? ' fixing errors ' : ' checking code ';

        $output->write($processInfo);
    }

}