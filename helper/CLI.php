<?php

use Rubika\Client;
use Rubika\Exception\Error;

function Bot(Closure $closure, int|string $phone)
{
    $GLOBALS["CLS"] = $closure;
    try {
        class myBot extends Client
        {
            public function onStart(): void
            {
            }

            public function runBot(array $update): void
            {
                $GLOBALS["CLS"]($update, $this);
            }
        }
        new myBot($phone);
    } catch (Error $e) {
        echo $e->getMessage() . "\n";
    }
}
