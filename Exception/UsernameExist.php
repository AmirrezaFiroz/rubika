<?php

namespace Rubika\Exception;

/**
 * account new username is already exists
 */
final class UsernameExist extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 13);
    }
}
