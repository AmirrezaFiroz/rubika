<?php

namespace Rubika\Exception;

/**
 * not understandable join link
 */
final class invalidJoinLink extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 19);
    }
}
