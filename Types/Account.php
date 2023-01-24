<?php

namespace Rubika\Types;

use Rubika\Exception\ERROR_GENERIC;
use Rubika\Exception\UsernameExist;
use Rubika\Tools\Crypto;
use Rubika\Extension\Traits;
use Rubika\Http\Kernel;
use stdClass;

/**
 * account infos
 */
class Account extends Traits
{
    /**
     * account auth
     *
     * @var string
     */
    public string $auth = '';

    /**
     * key for data crypto
     *
     * @var string
     */
    public string $encryptKey = '';

    /**
     * user info
     *
     * @var stdClass|null
     */
    public ?stdClass $user;

    /**
     * phone hash for read data file
     *
     * @var string
     */
    private string $ph_name = '';

    /**
     * @param boolean $readFile true if want to read data or false for init new data
     * @param array $data datas of account
     */
    final public function __construct(bool $readFile, array $data = [], int $phone = 0)
    {
        $this->ph_name = sha1((string)$phone);
        if ($data != []) {
            $this->config($data, true);
            if (!defined('SET_UP')) {
                define('SET_UP', false);
            }
        } else {
            if ($readFile) {
                define('SET_UP', false);
                $cnf = unserialize(base64_decode(file_get_contents(".rubika_config/." . $this->ph_name . ".base64")));
            } else {
                define('SET_UP', true);
                $auth = Traits::rand_str();
                $encryptKey = Crypto::create_secret($auth);
                $cnf = [
                    'auth' => $auth,
                    'encryptKey' => $encryptKey,
                    'user' => [
                        'user_guid' => ''
                    ]
                ];
                file_put_contents(".rubika_config/." . $this->ph_name . ".base64", base64_encode(serialize([
                    'auth' => $auth,
                    'encryptKey' => $encryptKey,
                    'user' => [
                        'user_guid' => ''
                    ]
                ])));
            }
            $this->config($cnf);
        }
    }

    /**
     * set account data
     *
     * @param array $data account data
     * @return void
     */
    private function config(array $data, bool $save = false): void
    {
        foreach ($data as $key => $value) {
            @$this->{$key} = is_array($value) ? $this->getObject($value) : $value;
        }
        if ($save) {
            file_put_contents(".rubika_config/." . $this->ph_name . ".base64", base64_encode(serialize($data)));
        }
    }

    /**
     * change array type to object
     *
     * @param array $array
     * @return stdClass
     */
    private function getObject(array $array): stdClass
    {
        $obj = new stdClass();
        foreach ($array as $key => $value) {
            $obj->{$key} = is_array($value) ? $this->getObject($value) : $value;
        }
        return $obj;
    }

    /**
     * get datas in aray type
     *
     * @return array
     */
    public function to_array(): array
    {
        return (array)$this;
    }

    /**
     * log out account session
     *
     * @return void
     */
    public function logout(): void
    {
        Kernel::send('logout', [], $this);
        unlink(".rubika_config/." . $this->ph_name . ".base64");
    }

    /**
     * change account two-step password
     *
     * @param string $oldPass account password
     * @param string $newPass new password for account
     * @param string $hint hint of password
     * @return array|false array if is it successful or false if its failed
     */
    public function changePassword(string $oldPass, string $newPass, string $hint): array|false
    {
        return Kernel::send('getUserInfo', [
            "password" => $oldPass,
            "new_hint" => $hint,
            "new_password" => $newPass
        ], $this);
    }

    /**
     * change account username
     *
     * @param string $newUsername
     * @return array|false
     */
    public function changeUsername(string $newUsername): array|false
    {
        $res = Kernel::send('updateUsername', [
            "username" => $newUsername
        ], $this);
        $this->user->username = $res['status'] == 'OK' ? $newUsername : $this->user->username;
        switch ($res['status']) {
            case 'UsernameExist':
                throw new UsernameExist('username is already exist');
                break;
            case 'ERROR_GENERIC':
                throw new ERROR_GENERIC("invalid username input:\n  1. must start with characters\n  2. characters count must between 5-32\n  3. allowed chars: english characters(a-z , A-Z) and (_)");
                break;
        }
        return $res;
    }

    /**
     * get account sessions
     *
     * @return array|false
     */
    public function getMySessions(): array|false
    {
        return Kernel::send('getMySessions', array(), $this);
    }
}
