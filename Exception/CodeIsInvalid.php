<?php

namespace Rubika\Exception;

/**
 * login code is invalid
 */
final class CodeIsInvalid extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 9);
    }
}