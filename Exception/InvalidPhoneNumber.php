<?php

namespace Rubika\Exception;

/**
 * invalid phone input
 */
final class InvalidPhoneNumber extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 1);
    }
}
