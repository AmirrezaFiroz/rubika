<?php

namespace Rubika\Exception;

/**
 * can not find file
 */
final class fileNotFound extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 15);
    }
}
