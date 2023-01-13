<?php

namespace Rubika\Tools;

/**
 * output print speed
 */
class Printing
{
    /**
     * slow print
     *
     * @param string $text
     * @return void
     */
    static function slow(string $text): void
    {
        foreach (str_split($text) as $char) {
            usleep(50000);
            echo $char;
        }
    }
    
    /**
     * medium print
     *
     * @param string $text
     * @return void
     */
    public static function medium(string $text): void
    {
        foreach (str_split($text) as $char) {
            usleep(30000);
            echo $char;
        }
    }

    /**
     * fast print
     *
     * @param string $text
     * @return void
     */
    public static function fast(string $text): void
    {
        foreach (str_split($text) as $char) {
            usleep(3000);
            echo $char;
        }
    }
}
