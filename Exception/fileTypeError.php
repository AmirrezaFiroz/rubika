<?php

namespace Rubika\Exception;

/**
 * file mie is invalid
 */
final class fileTypeError extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 16);
    }
}
