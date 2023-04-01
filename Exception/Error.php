<?php

namespace Rubika\Exception;

use fast;
use Rubika\Tools\Color;

/**
 * class for specify client errors from code errors
 */
class Error extends \Exception
{
    public bool $Rubika = true;

    public function __construct(string $error, int $errno)
    {
        $first = true;
        foreach (explode("\n", $error) as $line) {
            $error = $first ? '' : $error;
            $error .= ($first ? '' : "\n") . (isset($GLOBALS['argv']) ? Color::color(" >_ ", background: 'red') : "  ") . Color::color(" $line ", background: 'red');
            $first = false;
        }
        parent::__construct((isset($GLOBALS['argv']) ? Color::color(" Error: ", background: 'red') : "Error:") . "\n$error", $errno);
    }
}
