<?php

namespace Rubika\Exception;

/**
 * not have an intenet connetion
 */
final class internetConnectionError extends Error
{
    public function __construct(string $error = 'not have internet connection')
    {
        parent::__construct($error, 3);
    }
}
