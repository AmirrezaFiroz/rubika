<?php

namespace Rubika\Tools;

/**
 * painting texts for commandline
 */
final class Color
{
  /**
   * pain text
   *
   * @param string $string text for painting
   * @param string $fore text color name :
   * 
   * black, dark_gray, blue, light_blue, light_green, cyan, white, yellow,
   * red, light_red, purple, light_purple, brown, light_gray, light_cyan, green
   * @param string $background background of text
   * @return string
   */
  public static function color(string $string, string $fore = 'black', string $background = ""): string
  {
    [$fore, $background] = [
      strtolower(str_replace(' ', '', $fore)),
      strtolower(str_replace(' ', '', $background))
    ];

    $colored_string = "";

    $fore = "\033[" . match ($fore) {
      'black' => '0;30',
      'dark_gray' => '1;30',
      'blue' => '0;34',
      'light_blue' => '1;34',
      'green' => '0;32',
      'light_green' => '1;32',
      'cyan' => '0;36',
      'light_cyan' => '1;36',
      'red' => '0;31',
      'light_red' => '1;31',
      'purple' => '0;35',
      'light_purple' => '1;35',
      'brown' => '0;33',
      'yellow' => '1;33',
      'light_gray' => '0;37',
      'white' => '1;37'
    };

    if (!is_null(str_replace("\033[", '', $fore))) {
      $colored_string .= $fore . "m";
    }

    if (!empty($background)) {
      $colored_string .= "\033[" . match ($background) {
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
      }
        . "m";
    }

    $colored_string .= $string . "\033[0m";
    return $colored_string;
  }
}
