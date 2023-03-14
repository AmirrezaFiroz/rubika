<?php

declare(strict_types=1);
use Rubika\Client;
use Rubika\Exception\Error;

/**
 * run Client faster
 *
 * @param callable $callback a function to pass update and Bot object:
 * function callback(array $update, Bot $obj): void
 * {
 *    // code ...
 * }
 * @param integer $phone like 9123456789
 * @return void
 */
function Fast(callable $callback, int $phone): void
{
    $GLOBALS['callback_fn'] = $callback;
    try {
        class fastBot extends Client
        {
            function onStart(): void
            {
                // pass
            }

            function runBot(array $update): void
            {
                $GLOBALS['callback_fn']($update, $this);
            }
        }
    } catch (Error $th) {
        echo $th->getMessage() . "\n";
    }
}
