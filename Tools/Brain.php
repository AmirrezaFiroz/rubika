<?php

declare(strict_types = 1);
namespace Rubika\Tools;

use Rubika\Extension\Traits;

/**
 * encrypt data between API and client
 */
final class Brain
{
    public function __construct(
        public string $key
    )
    {}
    
    /**
     * encode data
     *
     * @param string $data json encoded data
     * @return string encrypted data
     */
    public function encrypt(string $data) : string
    {
        return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, str_repeat(chr(0x0), 16)));
    }
    
    /**
     * decode response data
     *
     * @param string $response encrypted data
     * @return string|false false if key in not true else returns json encoded data
     */
    public function decrypt(string $response) : string|false
    {
        return openssl_decrypt(base64_decode($response), 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA);
    }

    /**
     * create secred from auth for encrypt and decrypt data
     *
     * @param string $auth auth of account
     * @return string secret key
     */
    public static function create_secret(string $auth) : string
    {
        $t = mb_substr($auth, 0, 8);
        $i = mb_substr($auth, 8, 8);
        $n = mb_substr($auth, 16, 8) . $t . mb_substr($auth, 24, 8) . $i;

        for ($s = 0; $s < mb_strlen($n); $s++) {
            $e = $n[$s];
            if ($e >= "0" && $e <= "9") {
                $char = ((((mb_ord($e[0]) - 48) + 5) % 10) + 48);
            } else {
                $char = ((((mb_ord($e[0]) - 97) + 9) % 26) + 97);
            }
            $t = mb_chr($char);
            $n = Traits::replace($n, $s, $t);
        }
        return $n;
    }
}
