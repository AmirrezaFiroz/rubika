<?php

use Rubika\Bot;

/**
 * run web mode
 *
 * @param integer $phone
 * @return Bot
 */
function Web(int $phone)
{
    $b = new Bot($phone, true);
}
