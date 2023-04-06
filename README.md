# rubika

rubika client for running bots with PHP.
**use this client to make bots, games and ...**

# usage

run this command on terminal

```
composer require rubika/app
```

1. create a new php file in current directory

2. require vendor and **Bot** class in file
```php
require_once __DIR__ . '/vendor/autoload.php';

use Rubika\Bot;
```

3. now you can send messages
```php
$bot = new Bot(9123456789);
$bot->sendMessage('u0FFeu...', 'سلام');
```

# get message updates

for getting updates, you must create new class with a name and call it
```php
require_once __DIR__ . '/vendor/autoload.php';

use Rubika\Client;

class myBot extends Client
{
    function onStart(): void # required !
    {
        echo 'bot running ... ';
    }

    function runBot(array $update) # required !
    {
        foreach ((isset($update['message']) ? $update['message'] : $update) as $message) {
            $message = isset($message['message']) ? $message['message'] : $message;
            $msg_id = $message['message_id'];
            $text = $message['text'];
            $type = $message['type'];
            $user_id = $message['author_object_guid'];

            $this->sendMessage($user_id, 'پیامتان دریافت شد ;)');
        }
    }
}

new myBot(9123456789);
```

# error exceptions

we created exception system for all possible errors
you can catch them with try/catch :

```php
use Rubika\Exception\Error;

try {
    $bot = new Bot(9123456789);
    $bot->sendMessage('u0FFeu...', 'سلام');
} catch (Error $e) {
    echo $e->getMessage();
}


// or for updates :


try {
    new myBot(9206634543);
} catch (Error $e) {
    echo $e->getMessage();
}

```


| code  |                                      describtion                                      |
| :---: | :-----------------------------------------------------------------------------------: |
|   1   |                                  invalid phone input                                  |
|   2   |                            possible API errors (response)                             |
|   3   |                             not have an intenet connetion                             |
|   4   |                                   API general error                                   |
|   5   |                             invalid login code **input**                              |
|   6   |                          invalid twostep verifition password                          |
|   7   |                              login code time is expired                               |
|   8   |                           account session terminated error                            |
|   9   |                                 login code is invalid                                 |
|  10   |               account session terminated error **(in web login mode)**                |
|  11   |                                invalid message options                                |
|  12   |                           library cant find web index file                            |
|  13   | account new username is already exists on server and you can't set it as new username |
|  14   |                                    invalid action                                     |
|  15   |                                    not file exists                                    |
|  16   |                                  file mie is invalid                                  |
|  17   |                                  invalid data passed                                  |
|  18   |                             not understandable object ID                              |
|  19   |                                   invalid join link                                   |
|  20   |                                   invalid username                                    |
|  21   |                                     invalid email                                     |

# web mode

if you runs your bot on web page or want to make web page, we have a way too ;)

**Note :** with runnig on web page, Bot will active web mode automatic

```php
require_once __DIR__ . '/vendor/autoload.php';

$page = Web(9123456789);
// $page = Web(9123456789, 'index.php'); you can add a custom index file
// index file:
//     <?php
//     echo 'its OK ;)';
//     ?>

$page->sendMessage("uFF...", 'سلام');
```

**\* web login feature will improve on the text versions ...**

# fast mode

you can get message updates without writing class or ...
```php
require_once __DIR__ . '/vendor/autoload.php';

Fast(function ($update, $obj) {
    foreach ((isset($update['message']) ? $update['message'] : $update) as $message) {
        $message = isset($message['message']) ? $message['message'] : $message;
        $msg_id = $message['message_id'];
        $text = $message['text'];
        $type = $message['type'];
        $user_id = $message['author_object_guid'];

        $obj->sendMessage($user_id, 'پیامتان دریافت شد ;)');
    }
}, 9123456789);
```
