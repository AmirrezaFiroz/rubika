<?php

namespace Rubika\Http;

/**
 * check something about internet
 */
class Status
{
    /**
     * check internet connection
     *
     * @return boolean
     */
    public static function connection(): bool
    {
        $connected = @fsockopen("www.rubika.ir", 80);
        if ($connected) {
            $is_conn = true;
            fclose($connected);
        } else {
            $is_conn = false;
        }
        return $is_conn;
    }
}
