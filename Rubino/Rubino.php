<?php

declare(strict_types=1);

namespace Rubino;

use Rubika\Bot;
use Rubika\Exception\{
    InvalidPhoneNumber,
    invalidUsername
};
use Rubika\Extension\Traits;
use Rubika\Http\Kernel;
use Rubika\Tools\Color;
use Rubika\Types\Account;

class Rubino
{
    private ?string $ph_name;
    public ?Account $account;

    /**
     * initialize rubino
     *
     * @param integer $phone
     */
    public function __construct(
        private int $phone
    ) {
        if (strlen((string)$phone) == 10) {
            Traits::start($phone, true);
            Bot::config();
            $b = new Bot($phone, log: false);
            $this->ph_name = sha1((string)$phone);
            $this->account = $b->account;
        } else {
            throw new InvalidPhoneNumber(Color::color(str_repeat(' ', 28) . "\n  invalid phone number ...  \n" . str_repeat(' ', 28), 'white', 'red'));
        }
    }

    public function getUsernameInfo(string $username): array|false
    {
        preg_match('/[a-zA-Z][a-zA-Z0-9_]{2,31}/', str_replace(['@', ' '], '', $username), $matches);

        if (count($matches) == 0) {
            throw new invalidUsername("invalid username");
        }

        return Kernel::send('isExistUsername', [
            'username' => $matches[0]
        ], $this->account, rubino: true);
    }
}
