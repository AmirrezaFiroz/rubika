<?php

namespace Rubika\Exception;

/**
 * invalid phone options
 */
final class invalidOptions extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 11);
    }
}
