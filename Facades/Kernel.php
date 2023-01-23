<?php

namespace Rubika\Facades;

use Exception;

abstract class Kernel
{
    /**
     * @throws Exception
     */
    public static function setNameSpace(): string
    {
        throw new Exception("error");
    }

    public static function __callStatic(string $name, array $args = [])
    {

        $instance = new (static::setNameSpace())();
        return $instance->$name(...$args);
    }
}
