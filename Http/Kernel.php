<?php

declare(strict_types=1);
namespace Rubika\Http;

use Rubika\Tools\{
    Color,
    Brain,
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
 * HTTP library for sending requests
 */
class Kernel
{
    /**
     * get web page content
     *
     * @param string $url page url
     * @throws internetConnectionError not connected to internet
     * @return string page content
     */
    public static function Get(string $url): string
    {
        if (Status::connection()) {
            return file_get_contents($url);
        } else {
            throw new internetConnectionError('not connected to internet');
        }
    }

    /**
     * send POST request
     *
     * @param string $url
     * @param array $data array of data
     * @throws internetConnectionError not connected to internet
     * @return string return results
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
            throw new internetConnectionError('not connected to internet');
        }
    }

    /**
     * check availablity of url
     *
     * @param string $url
     * @throws internetConnectionError not connected to internet
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
                    'Connection: keep-alive'
                ],
            ]);
            curl_exec($c);
            $httpCode = curl_getinfo($c)['http_code'];
            curl_close($c);
            return $httpCode == 200 ? true : false;
        } else {
            throw new internetConnectionError('not connected to internet');
        }
    }

    /**
     * send data to server for running method
     *
     * @param string|Account $auth account auth or account info(in Account type)
     * @param array|object $datas request data
     * @param boolean $setTmpSession if true send tmp_session instead of auth in request
     * @throws internetConnectionError not connected to internet
     * @throws APIError server not returned a response
     * @return array|false array if result is successful
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
                        'data_enc' => (new Brain($key))->encrypt(json_encode($datas))
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
                            $response = (new Brain($key))->decrypt($response['data_enc']);
                            return json_decode($response, true);
                        } else {
                            return false;
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
            throw new internetConnectionError('not connected to internet');
        }
    }

    /**
     * run methods
     *
     * @param string $method
     * @param array $data
     * @return array|false array if result is successful
     */
    public static function send(string $method, array $data, Account $account, bool $setTmpSession = false): array|false
    {
        $r = self::send_request($account, [
            'method' => $method,
            'input' => $data,
            'client' => [
                "app_name" => "Main",
                "app_version" => "4.2.0",
                "platform" => "Web",
                "package" => "web.rubika.ir",
                "lang_code" => "fa"
            ]
        ], $setTmpSession);
        return isset($r['data']) ? $r['data'] : $r;
    }

    /**
     * get available socket links
     *
     * @return array list of links
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

    /**
     * request server for uploading a file
     *
     * @param string $file_namename of file
     * @param integer $size size of file
     * @return array|false array if is it successful or false if its failed
     */
    public static function requestSendFile(string $file_name, Account $acc, int $size): array|false
    {
        $e = explode(".", $file_name);
        return self::send('requestSendFile', [
            "file_name" => $file_name,
            "size" => $size,
            "mime" => end($e)
        ], $acc);
    }

    /**
     * upload file to server
     *
     * @param string $url upload url
     * @param integer $size file size (in byte)
     * @param string $access_hash_send
     * @param string $fid file id
     * @param string $content file content
     * @param Account $Acc account datas for getting auth of account
     * @throws APIError connection error
     * @return string returns access_hash_rec of file
     */
    public static function uploadFile(string $url, int $size, string $access_hash_send, string $fid, string $content, Account $Acc): string
    {
        if (!self::is_on($url)) {
            throw new APIError('server not responsed');
        }
        $size = (string)$size;
        $headers = [
            'access-hash-send' => $access_hash_send,
            'auth' => $Acc->auth,
            'chunk-size' => $size,
            'file-id' => $fid,
            'part-number' => '1',
            'total-part' => '1'
        ];
        $size = (int)$size;
        if ($size <= 131072) {
            $c = curl_init($url);
            curl_setopt_array($c, [
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array_map(function ($key) use ($headers) {
                    return "$key: {$headers[$key]}";
                }, array_keys($headers)),
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $content
            ]);
            $r = json_decode(curl_exec($c), true);
            curl_close($c);
            return $r['data']['access_hash_rec'];
        } else {
            $total = (int)($size / (131072 + 1));
            for ($i = 1; $i <= $total; $i++) {
                $which_chunk_now = ($i - 1) * 131072;
                $header["chunk-size"] = ($i != $total ? "131072" : (string)strlen(substr($content, $which_chunk_now)));
                $header["part-number"] = (string)$i;
                $header["total-part"] = (string)$total;
                $data = ($i != $total ? substr($content, $which_chunk_now, $which_chunk_now + 131072) : substr($content, $which_chunk_now));
                $c = curl_init($url);
                curl_setopt_array($c, [
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => array_map(function ($key) use ($headers) {
                        return "$key: {$headers[$key]}";
                    }, array_keys($headers)),
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data
                ]);
                $r = json_decode(curl_exec($c), true);
                curl_close($c);
                if ($i == $total) {
                    return $r['data']['access_hash_rec'];
                }
            }
        }
    }
}
