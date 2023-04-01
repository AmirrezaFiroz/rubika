<?php

namespace Rubika\Types;

/**
 * get actions of sendChatACtion method
 */
enum Actions: string
{
    case Typing = 'Typing';
    case Uploading = 'Uploading';
    case Recording = 'Recording';
}