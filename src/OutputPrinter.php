<?php

declare(strict_types = 1);

namespace Rulezilla;

use SebastianBergmann\Timer\Duration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function is_array;
use function mb_strtoupper;
use function sprintf;

use const PHP_EOL;

final class OutputPrinter

{

    public const ERROR_MESSAGE = '👿 Oops! There are some errors. I believe you in fixing!';
    public const SUCCESS_MESSAGE = '👍 It`s looks so good! Good work!';

    public static function printResult(SymfonyStyle $output, int $resultCode, array $cliOutput, bool $isDebugMode, Duration $duration, bool $isFixer, bool $parallel, string $processCommand, bool $isOK): void
    {
        $isSuccess = $resultCode === Command::SUCCESS;

        self::printCliOutput($output, $isSuccess, $isFixer, $parallel, $cliOutput, $duration, $isDebugMode, $isOK);

        if ((!$isDebugMode && !$isSuccess) || $parallel) {
            return;
        }

        if (!$isDebugMode) {
            return;
        }

        $output->note(sprintf('[Command] %s', $processCommand));

    }

    public static function printHeader(SymfonyStyle $output, bool $isFixer, bool $parallel, string $configKey, bool $isFastCheck): void
    {
        if ($parallel) {
            return ;
        }

        $output->title(
            sprintf('%s [%s%s:%s] it will take a while ...', $isFixer ? '🛠️ ' : '🔎', $isFastCheck ? 'FAST | ' : null, mb_strtoupper($configKey), $isFixer ? 'FIX' : 'CHECK'),
        );
    }

    public static function getDurationString(Duration $duration): string
    {
        return sprintf('[Executing time] %s', $duration->asString());
    }

    private static function printCliOutput(SymfonyStyle $output, bool $isSuccess, bool $isFixer, bool $parallel, array|string $cliOutput, Duration $duration, bool $isDebugMode, bool $isOK): void
    {
        if ($parallel && !$isFixer && !$isSuccess) {
            self::printRawCliOutput($output, $cliOutput);
        } elseif (!$parallel) {

            if ($isSuccess) {
                $output->success(sprintf('%s %s', self::SUCCESS_MESSAGE, self::getDurationString($duration)));
            } elseif (!$isOK) {
                self::printRawCliOutput($output, $cliOutput);
                $output->getErrorStyle()->error(sprintf('%s %s', self::ERROR_MESSAGE, self::getDurationString($duration)));
            } else {
                $output->success(sprintf('%s %s', self::SUCCESS_MESSAGE, self::getDurationString($duration)));
            }

            if ($isDebugMode) {
                self::printRawCliOutput($output, $cliOutput);
            }

        }

    }

    private static function printRawCliOutput(SymfonyStyle $output, array|string $cliOutput): void
    {
        $output->writeln(is_array($cliOutput) ? implode(PHP_EOL, $cliOutput) : $cliOutput);
    }

}