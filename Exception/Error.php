<?php

namespace Rubika\Exception;

/**
 * class for specify client errors from code errors
 */
class Error extends \Exception
{
    public bool $Rubika = true;

    public function __construct(string $error, int $errno)
    {
        parent::__construct($error, $errno);
    }
}
