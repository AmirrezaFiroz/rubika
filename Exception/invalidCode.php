<?php

namespace Rubika\Exception;

/**
 * invalid login code input
 */
final class invalidCode extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 5);
    }
}
