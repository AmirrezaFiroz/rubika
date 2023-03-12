<?php

declare(strict_types=1);

namespace Rubika;

use Exception;
use fast;
use Rubika\assets\login;
use Rubika\Exception\{
    CodeIsExpired,
    CodeIsInvalid,
    InvalidPhoneNumber,
    ERROR_GENERIC,
    invalidCode,
    invalidOptions,
    invalidPassword,
    noIndexFileExists,
    notRegistered,
    web_ConfigFileError
};
use Rubika\Extension\Traits;
use Rubika\Http\Kernel;
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
    public ?Account $account;

    private string $ph_name;

    public function __construct(
        private int $phone,
        $index = ''
    ) {
        if (strlen((string)$phone) == 10) {
            $this->ph_name = sha1((string)$phone);
            if (!isset($GLOBALS['argv'])) {
?>
                <!DOCTYPE html>
                <html>
                <script src="Rubika/assets/script.js"></script>
                <?php
                $this->config(false);
                $ex = file_exists(".rubika_config/." . $this->ph_name . ".base64");
                if ($ex) {
                    $acc = new Account(true, phone: $phone);
                } else {
                    $acc = new Account(false, phone: $phone);
                }
                $this->account = $acc;
                ?>
                <?php
                if (!$ex) {
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
                } elseif ($ex && $_POST == []) {
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
                            unlink(".rubika_config/." . $this->ph_name . ".base64");
                            throw new notRegistered("session has been terminated \n  please reload to try login");
                        }
                    }
                } elseif ($ex && isset($_POST['password']) && $_POST['password'] != '') {
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
                } elseif ($ex && isset($_POST['code']) && $_POST['code'] != '') {
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
                        $acc = new Account(false, $result, $phone);
                        $this->account = $acc;
                        $this->registerDevice($acc);
                    } else {
                        throw new web_ConfigFileError('config file was deleted and re-setup');
                    }
                }
                if (file_exists($index)) {
                    require_once file_get_contents($index);
                } else {
                    throw new noIndexFileExists('invalid file');
                }
                ?>

                </html>
<?php
            } else {
                Traits::start($phone);
                $this->config();
                if (file_exists(".rubika_config/." . $this->ph_name . ".base64")) {
                    $acc = new Account(true, phone: $phone);
                } else {
                    $acc = new Account(false, phone: $phone);
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
                        $acc = new Account(false, $result, $phone);
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
                        unlink(".rubika_config/." . $this->ph_name . ".base64");
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
     * get account info
     *
     * @param boolean $array true for return array
     * @return Account|array
     */
    public function getMe(bool $array = false): Account|array
    {
        return $array ? $this->account->to_array() : $this->account;
    }

    /** 
     * get account sessions
     * 
     * @return array|false
     */
    public function getMySessions(): array|false
    {
        return Kernel::send('getMySessions', [], $this->account);
    }

    /**
     * logout account
     *
     * @return void
     */
    public function logout(): void
    {
        Kernel::send('logout', [], $this->account);
    }

    /** 
     * seen messages
     * 
     * @param array $seen_list list of message seened ['object_guid' => 'LAST MESSAGE ID FOR SEEN']
     * @return array|false
     */
    public function seenChats(array $seen_list): array|false
    {
        return Kernel::send('seenChats', [
            'seen_list' => $seen_list
        ], $this->account);
    }

    /**
     * send message to user
     *
     * @param string $guid user guid
     * @param string $text message
     * @param integer $reply_to_message_id reply to message id
     * @param array $options options of message. (like telegram markup)
     * examples:
     * https://rubika-library.github.io/docs/options
     * @return array|false
     */
    public function sendMessage(string $guid, string $text, int $reply_to_message_id = 0, array $options = []): array|false
    {
        $no = "\n\n";
        if ($options != []) {
            $index = mb_str_split($options['index']);
            unset($options['index']);
            if (count($index) >= 1 && count($index) <= 3) {
                foreach ($options as $nu => $opt) {
                    $no .= "{$index[0]} $nu {$index[1]} {$index[2]} $opt";
                }
            } else {
                throw new invalidOptions("your options's arrange is invalid");
            }
        }
        $data = [
            'object_guid' => $guid,
            'rnd' => (string)mt_rand(100000, 999999),
            'text' => $text . $no
        ];
        if ($reply_to_message_id != 0) {
            $data['reply_to_message_id'] = $reply_to_message_id;
        }
        return Kernel::send('sendMessage', $data, $this->account);
    }

    /**
     * edit message in chat
     *
     * @param string $guid
     * @param integer $message_id message id for edit
     * @param string $text
     * @param array $options options of message. (like telegram markup)
     * examples:
     * https://rubika-library.github.io/docs/options
     * @return array|false
     */
    public function editMessage(string $guid, int $message_id,  string $text, array $options): array|false
    {
        $no = "\n\n";
        $index = mb_str_split($options['index']);
        unset($options['index']);
        if ($options != []) {
            $index = mb_str_split($options['index']);
            if (count($index) >= 1 && count($index) <= 3) {
                foreach ($options as $nu => $opt) {
                    $no .= "{$index[0]} $nu {$index[1]} {$index[2]} $opt";
                }
            } else {
                throw new invalidOptions("your options's arrange is invalid");
            }
        }
        $data = [
            'object_guid' => $guid,
            'message_id' => $message_id,
            'text' => $text . $no
        ];
        return Kernel::send('editMessage', $data, $this->account);
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
        return Kernel::send('deleteMessages', $data, $this->account);
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
        return Kernel::send('deleteMessages', $data, $this->account);
    }

    /**
     * pin message in chat
     *
     * @param string $guid chat guid
     * @param integer $message_id
     * @return array|false
     */
    public function pinMessage(string $guid, int $message_id): array|false
    {
        $data = [
            'object_guid' => $guid,
            'message_id' => $message_id,
            'action' => 'Pin'
        ];
        return Kernel::send('deleteMessages', $data, $this->account);
    }

    /**
     * unpin message in chat
     *
     * @param string $guid chat guid
     * @param integer $message_id
     * @return array|false
     */
    public function unPinMessage(string $guid, int $message_id): array|false
    {
        $data = [
            'object_guid' => $guid,
            'message_id' => $message_id,
            'action' => 'Pin'
        ];
        return Kernel::send('deleteMessages', $data, $this->account);
    }

    /**
     * get user infomation
     *
     * @param string $user_user_guid user guid
     * @return array|false array if is it successful or false if its failed
     */
    public function getUserInfo(string $user_user_guid): array|false
    {
        return Kernel::send('getUserInfo', ["user_user_guid" => $user_user_guid], $this->account);
    }

    /**
     * add new contact
     *
     * @param string $fname first name
     * @param string $lname last name
     * @param integer $phone phone number. (like: 9123456789)
     * @return array|false
     */
    public function addContact(string $fname, string $lname, int $phone): array|false
    {
        return Kernel::send('addAddressBook', [
            "first_name" => $fname,
            "last_name" => $lname,
            "phone" => "98" . (string)$phone
        ], $this->account);
    }

    /**
     * delete contact
     *
     * @param string $guid
     * @return array|false
     */
    public function deleteContact(string $guid): array|false
    {
        return Kernel::send('deleteContact', ["user_guid" => $guid], $this->account);
    }

    /**
     * get contact list
     *
     * @return array|false
     */
    public function getContacts(): array|false
    {
        return Kernel::send('getContacts', array(), $this->account);
    }

    /**
     * block the user
     *
     * @param string $guid
     * @return void
     */
    public function block(string $guid)
    {
        return Kernel::send('setBlockUser', [
            "user_guid" => $guid,
            "action" => "Block"
        ], $this->account);
    }

    /**
     * unblock blocked user
     *
     * @param string $guid
     * @return void
     */
    public function unBlock(string $guid)
    {
        return Kernel::send('setBlockUser', [
            "user_guid" => $guid,
            "action" => "Unblock"
        ], $this->account);
    }

    /**
     * mute chat notifocations
     *
     * @param string $guid chat id
     * @return array|false
     */
    public function muteChat(string $guid): array|false
    {
        return Kernel::send('setActionChat', [
            "action" => "Mute",
            "object_guid" => $guid
        ], $this->account);
    }

    /**
     * unmute muted chat notifocations
     *
     * @param string $guid chat id
     * @return array|false
     */
    public function unUuteChat(string $guid): array|false
    {
        return Kernel::send('setActionChat', [
            "action" => "Unmute",
            "object_guid" => $guid
        ], $this->account);
    }

    /**
     * get all chats, channels and groups
     *
     * @return array|false
     */
    public function getChats(): array|false
    {
        return Kernel::send('getChats', [], $this->account);
    }

    /**
     * get new updates
     *
     * @return array|false
     */
    public function getChatsUpdates(): array|false
    {
        return Kernel::send('getChatsUpdates', ['state' => time()], $this->account);
    }

    /** 
     * search text from a chat
     * 
     * @param string $object_guid grop or user or channel or ... id for search
     * @param string $search_text text for search
     * @param string $type:
     * Hashtag, Text
     * @return array|false
     */
    public function searchChatMessages(string $object_guid, string $search_text, string $type = 'Text'): array|false
    {
        return Kernel::send('searchChatMessages', [
            'search_text' => $search_text,
            'type' => $type,
            'object_guid' => $object_guid
        ], $this->account);
    }

    /** 
     * global seach to find a special user, channel or group
     *  
     * @param string $search_text text for search
     * @return array|false
     */
    public function searchGlobalObjects(string $search_text): array|false
    {
        return Kernel::send('searchGlobalObjects', ['search_text' => $search_text], $this->account);
    }

    /** 
     * global(in account) search for messages
     * 
     * @param string $search_text text for search
     * @param string $type:
     * Hashtag, Text
     * @return array|false
     */
    public function searchGlobalMessages(string $search_text, string $type): array|false
    {
        return Kernel::send('searchGlobalMessages', [
            'search_text' => $search_text,
            'type' => $type
        ], $this->account);
    }

    /**
     * send poll(just channel or group)
     *
     * @param string $guid user guid
     * @param string $question poll question
     * @param array $options
     * like : array(
     *    'option1',
     *    'option2'
     * );
     * @param boolean $allows_multiple_answers
     * @param boolean $is_anonymous
     * @param integer $reply_to_message_id
     * @return array|false
     */
    public function sendPoll(string $guid,  string $question, array $options, bool $allows_multiple_answers = false, bool $is_anonymous = true, int $reply_to_message_id = 0): array|false
    {
        $data = [
            'object_guid' => $guid,
            'rnd' => (string)mt_rand(100000, 999999),
            'question' => $question,
            'options' => $options,
            'allows_multiple_answers' => $allows_multiple_answers,
            'is_anonymous' => $is_anonymous,
            'type' => 'Regular'
        ];
        if ($reply_to_message_id != 0) {
            $data['reply_to_message_id'] = $reply_to_message_id;
        }

        return Kernel::send('createPoll', $data, $this->account);
    }

    /**
     * send quiz (just channel or group)
     *
     * @param string $guid user guid
     * @param string $question poll question
     * @param array $options
     * like : array(
     *    'option1',
     *    'option2'
     * );
     * @param boolean $correct_option_index the correct index of options.
     * notice: you must input an integer number that start from 0.
     * for example if you enter 1, you selected the second option
     * @param boolean $is_anonymous
     * @param integer $reply_to_message_id
     * @return array|false
     */
    public function sendQuiz(string $guid,  string $question, array $options, int $correct_option_index, bool $is_anonymous = true, int $reply_to_message_id = 0): array|false
    {
        $data = [
            'object_guid' => $guid,
            'rnd' => (string)mt_rand(100000, 999999),
            'question' => $question,
            'options' => $options,
            'correct_option_index' => $correct_option_index,
            'type' => 'Quiz'
        ];
        if ($reply_to_message_id != 0) {
            $data['reply_to_message_id'] = $reply_to_message_id;
        }

        return Kernel::send('createPoll', $data, $this->account);
    }

    /** 
     * get status of poll
     * 
     * @param string $poll_id
     * @return array|false
     */
    public function getPollStatus(string $poll_id): array|false
    {
        return Kernel::send('getPollStatus', ['poll_id' => $poll_id], $this->account);
    }


    /**
     * Undocumented function
     *
     * @param Account $acc account object
     * @return array|false array if is it successful or false if its failed
     */
    private function registerDevice(Account $acc): array|false
    {
        return Kernel::send(
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
    private function config(bool $log = true): void
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
        $servers = json_decode(Kernel::Get('https://getdcmess.iranlms.ir/'), true)['data'];
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
    private function set_configs(array $data): void
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
    private function sendSMS(int $phone, Account $acc, string $password = ''): array|false
    {
        $i = [
            'phone_number' => '98' . (string)$phone,
            'send_type' => 'SMS'
        ];
        if (!empty($password)) {
            $i['pass_key'] = $password;
        }
        return Kernel::send('sendCode', $i, $acc, true);
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
    private function signIn(int $phone, Account $acc, string $hash, int $code): array|false
    {
        return Kernel::send('signIn', [
            "phone_number" => '98' . (string)$phone,
            "phone_code_hash" => $hash,
            "phone_code" => $code
        ], $acc, true);
    }
}
