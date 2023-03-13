<?php

namespace Rubika\Exception;

/**
 * invalid chat action
 */
final class invalidAction extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 14);
    }
}
