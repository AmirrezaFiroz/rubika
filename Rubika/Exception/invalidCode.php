<?php

namespace Rubika\Exception;

/**
 * invalid login code input
 */
final class invalidCode extends Error
{
    public function __construct(string $text)
    {
        parent::__construct($text, 5);
    }
}