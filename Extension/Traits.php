<?php

declare(strict_types=1);

namespace Rubika\Extension;

use Rubika\Tools\{
    Color,
    Printing,
    System
};

/**
 * functions for library
 */
class Traits
{
    use functions;
    use Texts;
}

trait functions
{
    /**
     * get random string
     *
     * @param integer $length length of string
     * @return string
     */
    public static function rand_str(int $length = 32): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, (strlen($chars) - 1))];
        }
        return $result;
    }

    /**
     * get random string from gived chars
     *
     * @param integer $length
     * @param string ...$chars
     * @return string
     */
    public static function rand_str_chars(int $length, string ...$chars): string
    {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $t = mt_rand(0, count($chars) - 1);
            $str .= str_split($chars[$t])[mt_rand(0, strlen($chars[$t]) - 1)];
        }
        return $str;
    }

    /**
     * replace character at indexes of string
     *
     * @param string $text string for editing
     * @param integer $position index
     * @param string $newChar replace char with ...
     * @return string
     */
    public static function replace(string $text, int $position, string $newChar): string
    {
        $text[$position] = $newChar;
        return $text;
    }
}

/**
 * default texts
 */
trait Texts
{
    /**
     * print welcome text
     *
     * @return void
     */
    public static function welcome(): void
    {
        System::clear();
        echo Color::color(" rubika PHP client started ... \n     ||\n     || ", 'light_purple') . Color::color(Configs::get_github(), 'light_red') . Color::color("\n     ||\n     \_ ", 'light_purple') . Color::color(Configs::get_telegram(), 'light_red') . "\n";
    }

    /**
     * print starting text
     *
     * @return void
     */
    public static function start(int $phone): void
    {
        System::clear();
        Printing::fast(Color::color(" starting >_ (98-" . substr_replace((string)$phone, '-****', 3, 4) . ")...", background: 'green') . "\n");
        sleep(1);
    }
}
