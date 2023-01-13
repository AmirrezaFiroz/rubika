<?php

namespace Rubika\Exception;

/**
 * login code expired error
 */
final class CodeIsExpired extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 7);
    }
}
