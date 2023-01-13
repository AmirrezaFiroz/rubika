<?php

use Rubika\Bot;
use Rubika\Exception\Error;

function Web(int $phone): Bot
{
    try {
        return new Bot($phone, true);
    } catch (Error $e) {
    }
}
