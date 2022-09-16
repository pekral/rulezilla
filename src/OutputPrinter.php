<?php

declare(strict_types = 1);

namespace Rulezilla;

use SebastianBergmann\Timer\Duration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function is_array;
use function ucfirst;

use const PHP_EOL;

final class OutputPrinter
{

    public const ERROR_MESSAGE = '<error>Oops! There are some errors. I believe you in fixing! 👿</error>';
    public const SUCCESS_MESSAGE = '<info>It`s looks so good! Good work! 👍</info>';

    public static function printResult(OutputInterface $output, int $resultCode, array $cliOutput, bool $isDebugMode, Duration $duration, bool $isFixer, bool $parallel, string $processCommand): void
    {
        $isSuccess = $resultCode === Command::SUCCESS;

        self::printCliOutput($output, $isSuccess, $isFixer, $parallel, $cliOutput, $duration);

        if ((!$isDebugMode && !$isSuccess) || $parallel) {
            return;
        }

        if (!$isDebugMode) {
            return;
        }

        $output->writeln('<info>[DEBUG]</info> [Command] '. $processCommand);

    }

    public static function printHeader(OutputInterface $output, bool $isFixer, bool $parallel, string $configKey): void
    {
        if ($parallel) {
            return ;
        }

        $output->write(ucfirst(mb_strtolower($configKey)));
        $output->write($isFixer ? ' fixing errors ' : ' checking code ');
        $output->write('it will take a while ... ');
    }

    private static function printCliOutput(OutputInterface $output, bool $isSuccess, bool $isFixer, bool $parallel, array|string $cliOutput, Duration $duration): void
    {
        if ($parallel && !$isFixer && !$isSuccess) {
            $output->writeln($cliOutput);
        } elseif (!$parallel) {

            if ($isSuccess) {
                $output->write(sprintf('%s [Executing time] %s', self::SUCCESS_MESSAGE, $duration->asString()), true);
            } else {
                $output->writeln(is_array($cliOutput) ? implode(PHP_EOL, $cliOutput) : $cliOutput);
                $output->writeln(self::ERROR_MESSAGE);
            }

        }

    }

}