<?php

namespace Rubika\Exception;

/**
 * invalid email
 */
final class invalidEmail extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 21);
    }
}
