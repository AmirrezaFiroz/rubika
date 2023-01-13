<?php

namespace Rubika\Types;

use Rubika\Tools\Crypto;
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
     * key for data crypto
     *
     * @var string
     */
    public string $encryptKey = '';

    /**
     * user user_guid
     *
     * @var string
     */
    public string $user_guid = '';

    /**
     * @param boolean $readFile true if want to read data or false for init new data
     * @param array $data datas of account
     */
    final public function __construct(bool $readFile, array $data = [])
    {
        if ($data != []) {
            $this->config($data, true);
            if (!defined('SET_UP')) {
                define('SET_UP', false);
            }
        } else {
            if ($readFile) {
                define('SET_UP', false);
                $cnf = unserialize(base64_decode(file_get_contents('.rubika_config/.data.base64')));
            } else {
                $auth = Traits::rand_str();
                $encryptKey = Crypto::create_secret($auth);
                $cnf = [
                    'auth' => $auth,
                    'encryptKey' => $encryptKey,
                    'user' => [
                        'user_guid' => ''
                    ]
                ];
                define('SET_UP', true);
                file_put_contents('.rubika_config/.data.base64', base64_encode(serialize([
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
            file_put_contents('.rubika_config/.data.base64', base64_encode(serialize($data)));
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
}