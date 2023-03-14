<?php

namespace Rubika\Exception;

/**
 * invalid datas
 */
final class invalidData extends Error
{
    public function __construct(string $error)
    {
        parent::__construct($error, 17);
    }
}
