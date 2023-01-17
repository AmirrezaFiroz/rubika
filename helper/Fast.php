<?php

use Rubika\Client;
use Rubika\Exception\Error;

/**
 * get updates fast
 *
 * @param Closure $closure function for passing updates and client object:
 * function (array $update, client): void {}
 * @param integer|string $phone
 * @return void
 */
function Fast(Closure $closure, int|string $phone)
{
    $GLOBALS["CLS"] = $closure;
    try {
        class fast extends Client
        {
            public function onStart(): void
            {
            }

            public function runBot(array $update): mixed
            {
                return $GLOBALS["CLS"]($update, $this);
            }
        }
        new fast($phone);
    } catch (Error $e) {
        echo $e->getMessage() . "\n";
    }
}
