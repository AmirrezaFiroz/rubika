<?php

declare(strict_types=1);

namespace Rubino;

use Rubika\Bot;
use Rubika\Exception\InvalidPhoneNumber;
use Rubika\Exception\notRegistered;
use Rubika\Extension\Traits;
use Rubika\Tools\Color;
use Rubika\Tools\System;
use Rubika\Types\Account;

class Rubino
{
    private ?string $ph_name;
    public ?Account $account;

    public function __construct(
        private int $phone
    ) {
        if (strlen((string)$phone) == 10) {
            Traits::start($phone, true);
            Bot::config();
            $this->ph_name = sha1((string)$phone);
            
            if (file_exists(".rubika_config/." . $this->ph_name . ".base64")) {
                $acc = new Account(true, phone: $phone);
            } else {
                $acc = new Account(false, phone: $phone);
            }
            $b = new Bot($phone, log: false);
            $this->account = $acc;
        } else {
            throw new InvalidPhoneNumber(Color::color(str_repeat(' ', 28) . "\n  invalid phone number ...  \n" . str_repeat(' ', 28), 'white', 'red'));
        }
    }
}
