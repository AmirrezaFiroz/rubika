<?php

namespace Rubika\Exception;

/**
 * login code is invalid
 */
class CodeIsInvalid extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 9);
    }
}