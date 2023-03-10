<?php

declare(strict_types=1);

namespace Rubika\Http;

use Rubika\Tools\{
    Color,
    Crypto,
    System
};
use Rubika\Exception\{
    APIError,
    internetConnectionError
};
use Rubika\Types\Account;
use Symfony\Component\Yaml\Yaml;
use WebSocket\Client as websocket;
use WebSocket\ConnectionException;

/**
 * HTTP library
 */
class Kernel
{
    /**
     * send GET request
     *
     * @param string $url
     * @return string
     */
    public static function Get(string $url): string
    {
        if (Status::connection()) {
            return file_get_contents($url);
        } else {
            throw new internetConnectionError();
        }
    }

    /**
     * send POST request
     *
     * @param string $url
     * @param array $data data in array type
     * @return string
     */
    public static function Post(string $url, array $data): string
    {
        if (Status::connection()) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36 Edg/108.0.1462.46',
                    'Referer: https://web.rubika.ir/',
                    'Origin: https://web.rubika.ir',
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data)
            ]);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        } else {
            throw new internetConnectionError();
        }
    }

    /**
     * testing is url available
     *
     * @param string $url
     * @return boolean true for available and false if isn't available
     */
    public static function is_on(string $url): bool
    {
        if (Status::connection()) {
            $c = curl_init($url);
            curl_setopt_array($c, [
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_HEADER => true,
                CURLOPT_CUSTOMREQUEST => 'OPTIONS',
                CURLOPT_HTTPHEADER => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                    'Accept: */*',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate, br',
                    'Access-Control-Request-Method: POST',
                    'Access-Control-Request-Headers: content-type',
                    'Referer: https://web.rubika.ir/',
                    'Origin: https://web.rubika.ir',
                    'Connection: keep-alive',
                ],
            ]);
            curl_exec($c);
            $httpCode = curl_getinfo($c)['http_code'];
            curl_close($c);
            return $httpCode == 200 ? true : false;
        } else {
            throw new internetConnectionError();
        }
    }

    /**
     * send requests to rubika
     *
     * @param string|Account $auth account auth or account info(in Account type)
     * @param array|object $datas request data
     * @param boolean $setTmpSession true for replace 'tmp_session' instead of 'auth' in request
     * @return array|false array if is it successful or false if its failed
     */
    public static function send_request(Account $account, array|object $datas, bool $setTmpSession = false): array|false
    {
        if (Status::connection()) {
            $key = $account->encryptKey;
            $urls = [];
            $yaml = Yaml::parse(file_get_contents('.rubika_config/.servers.yaml'))['API'];
            foreach ($yaml as $number => $link) {
                unset($yaml[$number]);
                $yaml["$number."] = $link;
            }
            for ($i = 0; $i < count($yaml); $i++) {
                $urls[] = $yaml[mt_rand(101, 153) . "."];
            }
            $sended = false;
            $i = 0;
            foreach ($urls as $url) {
                if (self::is_on($url)) {
                    unset($urls[$i]);
                    $data = [
                        'api_version' => '5',
                        'data_enc' => (new Crypto($key))->encrypt(json_encode($datas))
                    ];
                    $data[$setTmpSession ? 'tmp_session' : 'auth'] = $account->auth;
                    try {
                        $response = self::Post($url, $data);
                    } catch (APIError $e) {
                        foreach ($urls as $newUrl) {
                            $response = self::Post($newUrl, $data);
                            if (is_array(json_decode($response, true))) {
                                break;
                            }
                        }
                    }
                    if (is_array(json_decode($response, true))) {
                        $response = json_decode($response, true);
                        if (isset($response['data_enc'])) {
                            $response = (new Crypto($key))->decrypt($response['data_enc']);
                            return json_decode($response, true);
                        } else {
                            return false; // make it an error
                        }
                    } else {
                        if (!isset($GLOBALS['argv'])) {
                            throw new APIError(Color::color(' rubika has returned error : ', 'black', 'yellow') . "\n\t'" . $response . "' .");
                        } else {
                            throw new APIError(' rubika has returned error : ' . "\n\t'" . $response . "' .");
                        }
                    }
                } else {
                    unset($urls[$i]);
                }
                $i++;
            }
            return $sended;
        } else {
            throw new internetConnectionError();
        }
    }

    /**
     * run methods
     *
     * @param string $method
     * @param array $data
     * @return array|false  array if is it successful or false if its failed
     */
    public static function send(string $method, array $data, Account $account, bool $setTmpSession = false): array|false
    {
        $r = self::send_request($account, [
            'method' => $method,
            'input' => $data,
            'client' => [
                "app_name" => "Main",
                "app_version" => "4.1.11",
                "platform" => "Web",
                "package" => "web.rubika.ir",
                "lang_code" => "fa"
            ]
        ], $setTmpSession);
        return isset($r['data']) ? $r['data'] : $r;
    }

    /**
     * get available sockets
     *
     * @return array
     */
    public static function get_socket_links(): array
    {
        $links = Yaml::parse(file_get_contents('.rubika_config/.servers.yaml'))['socket'];
        System::clear();
        foreach ($links as $t => $link) {
            try {
                $client = new websocket($link, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                        'origin' => 'https://web.rubika.ir',
                    ],
                    'timeout' => 1
                ]);
                $client->text('{}');
                $client->close();
            } catch (ConnectionException $e) {
                unset($links[$t]);
            }
        }
        return $links;
    }
}
