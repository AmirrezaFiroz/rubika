<?php

namespace Rubika\Exception;

/**
 * account session terminated error
 */
final class web_ConfigFileError extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 8);
    }
}
