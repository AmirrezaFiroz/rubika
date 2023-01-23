<?php

namespace Rubika\Exception;

/**
 * web page not found
 */
final class noIndexFileExists extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 12);
    }
}
