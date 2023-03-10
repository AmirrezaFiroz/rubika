<?php

namespace Rubika\Tools;

/**
 * class to work with files
 */
final class File
{
    /**
     * get image thumbnail
     *
     * @param string $image_bytes file content it must read binary
     * @return string
     */
    public static function getThumbInline(string $image_bytes): string
    {
        $im = imagecreatefromstring($image_bytes);
        ob_start();
        imagepng($im);
        $im_data = ob_get_clean();
        $thumbnail = base64_encode($im_data);
        return $thumbnail;
    }
}
