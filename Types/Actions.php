<?php

namespace Rubika\Types;

/**
 * get actions of sendChatACtion method
 */
class Actions
{
    private string $action = '';

    function __construct(string $action)
    {
        $this->action = match (strtolower($action)) {
            'typing' => 'Typing',
            'Uploading' => 'Uploading',
            'Recording' => 'Recording'
        };
    }

    function value(): string
    {
        return $this->action;
    }
}
