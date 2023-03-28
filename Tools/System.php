<?php

namespace Rubika\Tools;

/**
 * system commands
 */
class System
{
    /**
     * clear command line
     *
     * @return void
     */
    public static function clear(): void
    {
        $os = php_uname('s');
        if (php_sapi_name() == 'cli') {
            if ($os == 'Windows NT') {
                if (isset($_SERVER['SHELL'])) {
                    system('clear');
                } else {
                    system('cls');
                }
            } else {
                system('clear');
            }
        }
    }

    /**
     * check is text start with a specified character
     *
     * @param string $text text for search
     * @param string $withWhat specified character
     * @return boolean
     */
    public static function startWith(string $text, string $withWhat): bool
    {
        $length = strlen($withWhat);
        return substr($text, 0, $length) === $withWhat;
    }
    /**
     * check is text ends with a specified character
     *
     * @param string $text text for search
     * @param string $withWhat specified character
     * @return boolean
     */
    public static function endsWith($text, $withWhat)
    {
        $length = mb_strlen($withWhat);
        if (!$length) {
            return true;
        }
        return mb_substr($text, -$length) === $withWhat;
    }
}
