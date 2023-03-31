<?php

declare(strict_types=1);

namespace Rubika\Types;

use Rubika\Tools\Brain;
use Rubika\Extension\Traits;
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
     * key for data cryption
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
            !defined('SET_UP') ? define('SET_UP', false) : null;
        } else {
            if ($readFile) {
                define('SET_UP', false);
                $cnf = unserialize(base64_decode(file_get_contents(".rubika_config/." . $this->ph_name . ".base64")));
            } else {
                define('SET_UP', true);
                $auth = Traits::rand_str();
                $encryptKey = Brain::create_secret($auth);
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
     * get datas in array type
     *
     * @return array account info
     */
    public function to_array(): array
    {
        return (array)$this;
    }
}
