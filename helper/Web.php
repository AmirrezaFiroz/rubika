<?php

use Rubika\Bot;
use Rubika\Exception\Error;

/**
 * run web mode
 *
 * @param integer $phone
 * @return Bot
 */
function Web(int $phone)
{
    try {
        return new Bot($phone, true);
    } catch (Error $e) {
?>
        <script>
            showError('<?php echo $e; ?>');
        </script>
<?php
    }
}
