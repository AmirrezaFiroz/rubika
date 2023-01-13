<?php

namespace Rubika\Exception;

/**
 * API general error
 */
final class ERROR_GENERIC extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 4);
    }
}