<?php

namespace Rubika\Exception;

/**
 * not understandable username
 */
final class invalidUsername extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 20);
    }
}
