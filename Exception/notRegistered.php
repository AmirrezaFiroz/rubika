<?php

namespace Rubika\Exception;

/**
 * account session terminated error
 */
final class notRegistered extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 8);
    }
}
