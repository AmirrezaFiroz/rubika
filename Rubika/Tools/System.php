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
}
