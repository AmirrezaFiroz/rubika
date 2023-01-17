<?php

declare(strict_types=1);

namespace Rubika;

use Exception;
use Rubika\assets\login;
use Rubika\Exception\{
    CodeIsExpired,
    CodeIsInvalid,
    InvalidPhoneNumber,
    ERROR_GENERIC,
    invalidCode,
    invalidPassword,
    notRegistered,
    web_ConfigFileError
};
use Rubika\Extension\Traits;
use Rubika\Http\Curl;
use Rubika\Tools\{
    Color,
    Crypto,
    Printing,
    System
};
use Rubika\Types\Account;
use WebSocket\Client as websocket;
use Symfony\Component\Yaml\Yaml;

class Bot
{
    protected ?Account $account;

    private string $ph_name;

    public function __construct(
        private int $phone,
        bool $runWeb = false
    ) {
        if (strlen((string)$phone) == 10) {
            $this->ph_name = md5((string)$phone);
            if (!isset($GLOBALS['argv']) or $runWeb) {
?>
                <!DOCTYPE html>
                <html>
                <script src="Rubika/assets/script.js"></script>
                <?php
                $this->config(false);
                if (file_exists(".rubika_config/.$this->ph_name.base64")) {
                    $acc = new Account(true, phone: $phone);
                } else {
                    $acc = new Account(false, phone: $phone);
                }
                $this->account = $acc;
                ?>
                <?php
                if ($_POST == []) {
                    if (empty($acc->user->user_guid)) {
                        $result = $this->sendSMS($phone, $acc);
                        if (isset($result['status']) && ($result['status'] == 'SendPassKey' or strtolower($result['status']) == "ok")) {
                            if ($result['has_confirmed_recovery_email']) {
                                new login('two-step', $result['hint_pass_key']);
                            } else {
                                new login('', base64_encode(json_encode($result)));
                            }
                        } else {
                            if (isset($result['client_show_message'])) {
                                throw new ERROR_GENERIC($result['client_show_message']);
                            } else {
                                throw new ERROR_GENERIC("some things went wrong ... . (rubika : {$result['status_det']})");
                            }
                        }
                    } else {
                        $m = $this->getUserInfo($this->account->user->user_guid);
                        if (isset($m['status_det']) && $m['status_det'] == 'NOT_REGISTERED') {
                            unlink(".rubika_config/.$this->ph_name.base64");
                            throw new notRegistered("session has been terminated \n  please run again to login");
                        }
                    }
                } elseif (isset($_POST['password']) && $_POST['password'] != '') {
                    if (!SET_UP && empty($acc->user->user_guid)) {
                        $result = $this->sendSMS($phone, $acc, $_POST['password']);
                        if ($result['status'] == 'InvalidPassKey') {
                            throw new invalidPassword('two-step verifition password is not correct');
                        } else {
                            new login('', base64_encode(json_encode($result)));
                        }
                    } else {
                        throw new web_ConfigFileError('config file was deleted and re-setup');
                    }
                } elseif (isset($_POST['code']) && $_POST['code'] != '') {
                    if (!SET_UP && empty($acc->user->user_guid)) {
                        $callback = json_decode(base64_decode($_POST['data']), true);
                        $count = $callback['code_digits_count'];
                        $hash = $callback['phone_code_hash'];
                        $code = $_POST['code'];
                        $code = strlen((string)((int)$code)) == $count ? $code : '';
                        if (empty($code)) {
                            throw new invalidCode('code is not valid');
                        }
                        $result = $this->signIn($phone, $acc, $hash, (int)$code);
                        if ($result['status'] == 'CodeIsInvalid') {
                            throw new CodeIsInvalid('login code is not true');
                        } elseif ($result['status'] == 'CodeIsExpired') {
                            throw new CodeIsExpired(' login code is expired');
                        }
                        $result['encryptKey'] = Crypto::create_secret($result['auth']);
                        unset($result['status']);
                        unset($result['user_guid']);
                        $acc = new Account(false, $result);
                        $this->account = $acc;
                        $this->registerDevice($acc);
                ?>
                        <center>
                            <h1>
                                <strong>
                                    OK
                                </strong>
                            </h1>
                        </center>
                <?php
                    } else {
                        throw new web_ConfigFileError('config file was deleted and re-setup');
                    }
                } else {
                    var_dump($_POST);
                }
                ?>

                </html>
<?php
            } else {
                Traits::start($phone);
                $this->config();
                if (file_exists(".rubika_config/.$this->ph_name.base64")) {
                    $acc = new Account(true);
                } else {
                    $acc = new Account(false);
                }
                $this->account = $acc;
                if (empty($acc->user->user_guid)) {
                    $result = $this->sendSMS($phone, $acc);
                    if (isset($result['status']) && ($result['status'] == 'SendPassKey' or strtolower($result['status']) == "ok")) {
                        if ($result['has_confirmed_recovery_email']) {
                            do {
                                if (isset($do1)) {
                                    echo Color::color(" account has password ", background: 'green') . "\n" . Color::color("  please enter your password ({$result['hint_pass_key']})", 'light_green') . ' ' . Color::color('>', 'blue') . ' ';
                                } else {
                                    $do1 = true;
                                    Printing::fast(Color::color(" account has password ", background: 'green') . "\n" . Color::color("  please enter your password ({$result['hint_pass_key']})", 'light_green') . ' ' . Color::color('>', 'blue') . ' ');
                                }
                                $pass = readline();
                            } while (empty($pass));
                            $result = $this->sendSMS($phone, $acc, $pass);
                            if ($result['status'] == 'InvalidPassKey') {
                                throw new invalidPassword(Color::color(' two-step verifition password is not correct ', 'red'));
                            }
                            do {
                                if (isset($do2)) {
                                    echo Color::color('please enter SMS verifition code : ', 'light_green');
                                } else {
                                    $do2 = true;
                                    Printing::fast(Color::color('please enter SMS verifition code : ', 'light_green'));
                                }
                                $code = readline();
                            } while (empty($code));
                        } else {
                            do {
                                if (isset($do3)) {
                                    echo Color::color('please enter SMS verifition code : ', 'light_green');
                                } else {
                                    $do3 = true;
                                    Printing::fast(Color::color('please enter SMS verifition code : ', 'light_green'));
                                }
                                $code = readline();
                            } while (empty($code));
                        }
                        $count = $result['code_digits_count'];
                        $hash = $result['phone_code_hash'];
                        $code = strlen((string)((int)$code)) == $count ? $code : '';
                        if (empty($code)) {
                            throw new invalidCode(Color::color(' code is not valid ', background: 'red'));
                        }
                        $result = $this->signIn($phone, $acc, $hash, (int)$code);
                        if ($result['status'] == 'CodeIsInvalid') {
                            throw new CodeIsInvalid(Color::color(' login code is not true', 'red'));
                        } elseif ($result['status'] == 'CodeIsExpired') {
                            throw new CodeIsExpired(Color::color(' login code is expired', 'red'));
                        }
                        $result['encryptKey'] = Crypto::create_secret($result['auth']);
                        unset($result['status']);
                        unset($result['user_guid']);
                        $acc = new Account(false, $result);
                        $this->account = $acc;
                        $this->registerDevice($acc);
                    } else {
                        if (isset($result['client_show_message'])) {
                            $chars = '';
                            foreach (array_reverse(mb_str_split($result['client_show_message']['link']['alert_data']['message'])) as $char) {
                                $chars .= $char;
                            }
                            throw new ERROR_GENERIC(Color::color($chars, background: 'red') . "\n");
                        } else {
                            throw new ERROR_GENERIC("some things went wrong ... . (rubika : {$result['status_det']})");
                        }
                    }
                } else {
                    $m = $this->getUserInfo($this->account->user->user_guid);
                    if (isset($m['status_det']) && $m['status_det'] == 'NOT_REGISTERED') {
                        unlink(".rubika_config/.$this->ph_name.base64");
                        System::clear();
                        throw new notRegistered(Color::color("session has been terminated \n  please run again to login", background: 'red'));
                    }
                }
                Traits::welcome();
            }
        } else {
            throw new InvalidPhoneNumber(Color::color(str_repeat(' ', 28) . "\n  invalid phone number ...  \n" . str_repeat(' ', 28), 'white', 'red'));
        }
    }

    /**
     * send message to user
     *
     * @param string $guid user guid
     * @param string $text message
     * @param integer $reply_to_message_id reply to message id
     * @return array|false
     */
    public function sendMessage(string $guid, string $text, int $reply_to_message_id = 0): array|false
    {
        $data = [
            'object_guid' => $guid,
            'rnd' => (string)mt_rand(100000, 999999),
            'text' => str_replace(['**', '`', '__'], '', $text)
        ];
        if ($reply_to_message_id != 0) {
            $data['reply_to_message_id'] = $reply_to_message_id;
        }
        return Curl::send('sendMessage', $data, $this->account);
    }

    /**
     * delete message from chat
     *
     * @param string $guid
     * @param array|int $message_id array of ids or just one id
     * @param string $type delete global(Global) or local(Local)
     * @return array|false
     */
    public function deleteMessage(string $guid, array|int $message_id, string $type = 'Global'): array|false
    {
        $data = [
            'object_guid' => $guid,
            'type' => $type
        ];
        if (is_numeric($message_id)) {
            $data['message_ids'] = [
                $message_id
            ];
        } elseif (is_array($message_id)) {
            $data['message_ids'] = $message_id;
        }
        return Curl::send('deleteMessages', $data, $this->account);
    }

    /**
     * log out account session
     *
     * @return void
     */
    public function logout(): void
    {
        Curl::send('logout', [], $this->account);
        unlink(".rubika_config/.$this->ph_name.base64");
    }

    /**
     * get user infomation
     *
     * @param string $user_user_guid user guid
     * @return array|false array if is it successful or false if its failed
     */
    public function getUserInfo(string $user_user_guid): array|false
    {
        return Curl::send('getUserInfo', ["user_user_guid" => $user_user_guid], $this->account);
    }

    /**
     * Undocumented function
     *
     * @param Account $acc account object
     * @return array|false array if is it successful or false if its failed
     */
    private function registerDevice(Account $acc): array|false
    {
        return Curl::send(
            'registerDevice',
            [
                "token_type" => "Web",
                "token" => "",
                "app_version" => "WB_4.1.11",
                "lang_code" => "fa",
                "system_version" => 'Windows 10',
                "device_model" => 'Firefox 107',
                "device_hash" => "25010064641070201001011070"
            ],
            $acc
        );
    }

    /**
     * add config files and folders
     *
     * @return void
     */
    public function config(bool $log = true): void
    {
        if (!is_dir('.rubika_config') or !file_exists('.rubika_config/.servers.yaml')) {
            try {
                @mkdir('.rubika_config');
                if ($log) {
                    Printing::medium(Color::color(' adding servers ', 'yellow', 'green') . "\n");
                }
                $this->add_servers();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * add/update servers for using in client
     *
     * @return void
     * @throws Exception\internetConnectionError
     */
    private function add_servers(): void
    {
        $servers = json_decode(Curl::Get('https://getdcmess.iranlms.ir/'), true)['data'];
        file_put_contents(
            '.rubika_config/.servers.yaml',
            Yaml::dump($servers)
        );
    }

    /**
     * set new config to /.rubika_config/.[PHONE_HASH].base64 file
     *
     * @param array $data
     * @return void
     */
    public function set_configs(array $data): void
    {
        file_put_contents(".rubika_config/." . $this->ph_name . ".base64", base64_encode(serialize($data)));
    }

    /**
     * send login SMS to phone number
     *
     * @param integer $phone
     * @param Account $acc account object
     * @param string $password two-step verifition password
     * @return array|false array if is it successful or false if its failed
     */
    public function sendSMS(int $phone, Account $acc, string $password = ''): array|false
    {
        $i = [
            'phone_number' => '98' . (string)$phone,
            'send_type' => 'SMS'
        ];
        if (!empty($password)) {
            $i['pass_key'] = $password;
        }
        return Curl::send('sendCode', $i, $acc, true);
    }

    /**
     * signing to account
     *
     * @param integer $phone
     * @param Account $acc account object
     * @param string $hash phone_code_hash
     * @param integer $code phone_code
     * @return array|false array if is it successful or false if its failed
     */
    public function signIn(int $phone, Account $acc, string $hash, int $code): array|false
    {
        return Curl::send('signIn', [
            "phone_number" => '98' . (string)$phone,
            "phone_code_hash" => $hash,
            "phone_code" => $code
        ], $acc, true);
    }

    /** 
     * seen messages
     * 
     * @param array $seen_list list of message seened ['object_guid' => 'LAST MESSAGE ID FOR SEEN']
     * @return array|false
     */
    public function seenChats(array $seen_list): array|false
    {
        return Curl::send('seenChats', [
            'seen_list' => $seen_list
        ], $this->account);
    }

    /**
     * edit message in chat
     *
     * @param string $guid
     * @param integer $message_id message id for edit
     * @param string $text
     * @return array|false
     */
    public function editMessage(string $guid, int $message_id,  string $text): array|false
    {
        $data = [
            'object_guid' => $guid,
            'message_id' => $message_id,
            'text' => str_replace(['**', '`', '__'], '', $text)
        ];
        return Curl::send('editMessage', $data, $this->account);
    }

    /**
     * forward message from chat to another chat
     *
     * @param string $from_guid from chat
     * @param string $to_guid to chat
     * @param array|integer $message_id array of ids or one just id
     * @return array|false
     */
    public function forwardMessages(string $from_guid, string $to_guid, array|int $message_id): array|false
    {
        $data = [
            'from_object_guid' => $from_guid,
            'rnd' => (string)mt_rand(100000, 999999),
            'to_object_guid' => $to_guid
        ];
        if (is_numeric($message_id)) {
            $data['message_ids'] = [
                $message_id
            ];
        } elseif (is_array($message_id)) {
            $data['message_ids'] = $message_id;
        }
        return Curl::send('deleteMessages', $data, $this->account);
    }

    /**
     * get account info
     *
     * @param boolean $array true for return array
     * @return Account|array
     */
    public function getMe(bool $array = false): Account|array
    {
        return $array ? $this->account->to_array() : $this->account;
    }
}
