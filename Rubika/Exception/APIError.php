<?php

namespace Rubika\Exception;

/**
 * API response errors
 */
final class APIError extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 2);
    }
}