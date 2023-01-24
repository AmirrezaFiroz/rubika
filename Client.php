<?php

namespace Rubika;

use Rubika\Exception\internetConnectionError;
use Rubika\Http\Kernel;
use Rubika\Tools\Color;
use Rubika\Tools\Crypto;
use WebSocket\Client as websocket;

/**
 * run client with getupdates
 */
abstract class Client extends Bot
{
    /**
     * @throws Exception\ERROR_GENERIC
     * @throws Exception\invalidPassword
     * @throws Exception\notRegistered
     * @throws Exception\InvalidPhoneNumber
     * @throws Exception\invalidCode
     */
    public function __construct(int $phone, bool $runWeb = false)
    {
        parent::__construct($phone, $runWeb);
        $url = Kernel::get_socket_links();
        if (count($url) == 0) {
            throw new internetConnectionError(Color::color(' error in testing socket links ', background: 'red'));
        }
        $url = $url[array_rand($url, 1)];
        $this->onStart();
        $this->onUpdateMessage($url, $runWeb);
    }

    /**
     * function to anwser updates
     *
     * @param array $update
     * @return void
     */
    abstract function runBot(array $update);

    /**
     * run codes when client started
     *
     * @return void
     */
    abstract function onStart(): void;

    /**
     * when get update
     *
     * @param string $url webSocket url
     * @return void
     */
    private function onUpdateMessage(string $url): void
    {
        $socket = new websocket($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
                'origin' => 'https://web.rubika.ir',
            ],
            'timeout' => 60
        ]);
        $socket->text(json_encode([
            'api_version' => '5',
            'auth' => $this->account->auth,
            'data' => '',
            'method' => 'handShake'
        ]));
        $second = 0;
        do {
            if (($second % 15) === 0) {
                $socket->text('{}');
            }
            usleep(500000);
            $second++;
            $update = json_decode($socket->receive(), true);
            if (isset($update['data_enc'])) {
                $update = json_decode((new Crypto($this->account->encryptKey))->decrypt($update['data_enc']), true);
            }
            $this->runBot(isset($update['message_updates']) ? $update['message_updates'] : []);
        } while (true);
    }
}
