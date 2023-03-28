<?php

namespace Rubika\Exception;

/**
 * not understandable object ID
 */
final class invalidID extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 18);
    }
}
