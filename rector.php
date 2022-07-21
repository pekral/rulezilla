<?php

declare(strict_types = 1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

    foreach (include(__DIR__.'/src/Rector/rector.php') as $ruleClassName) {
        $rectorConfig->rule($ruleClassName);
    }
};