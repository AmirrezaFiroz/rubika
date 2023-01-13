<?php

namespace Rubika\Exception;

/**
 * invalid twostep verifition password
 */
final class invalidPassword extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 6);
    }
}