<?php

namespace Rubika\assets;

class login
{
    public function __construct(string $step = '', string $value = '')
    {
?>

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>ورود به حساب</title>
            <link rel="stylesheet" href="Rubika/assets/style.css">
        </head>

        <body>
            <div id="login">
                <div id="input">
                    <div id="step">
                        <strong>
                            <b>
                                <?php
                                echo match ($step) {
                                    'two-step' => 'گذرواژه ورود',
                                    '' => 'پیامک ورود'
                                };
                                $i = match ($step) {
                                    'two-step' => 'password',
                                    '' => 'code'
                                };
                                ?>
                            </b>
                        </strong>
                    </div>
                    <form method="post" action="">
                        <label for="<?php echo $i; ?>"></label>
                        <input type="<?php echo $i == 'code' ? 'number' : 'text'; ?>" id="<?php echo $i; ?>" name="<?php echo $i; ?>" placeholder="<?php echo $i == 'two-step' ? $value : $i; ?>">
                        <input type="text" id="data" name="data" value="<?php echo $value; ?>">
                        <button>برو</button>
                    </form>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}
