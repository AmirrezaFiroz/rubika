<?php

namespace Rubika\Exception;

/**
 * not have an intenet connetion
 */
final class internetConnectionError extends Error
{
    public function __construct(string $error = 'not have internet connection')
    {
        parent::__construct(!isset($GLOBALS['argv']) ? str_repeat(' ', 25) . "\n  $error  \n" . str_repeat(' ', 25) : $error, 3);
    }
}
