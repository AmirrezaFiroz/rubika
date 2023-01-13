<?php

namespace Rubika\Facades;

//use
class Client extends Kernel{

    public static function setNameSpace(): string
    {
        return TheApp::class;
    }
}