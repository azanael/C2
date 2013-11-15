<?php

class C2_ImageConverter
{
    public function convert($src, $dest, $width, $height = null, $format = 'png', $crop = true)
    {
        if ($src === null || $dest === null) {
            throw new InvalidArgumentException('src or dest is null.');
        }
        $height = ($height == null) ? $width : $height;
        if (!is_dir(dirname($dest))) {
            $r = @mkdir(dirname($dest), 0777, true);
            if ($r === false) {
                throw new RuntimeException("Failed to create directory " . dirname($dest));
            }
            @chmod(dirname($dest), 0777);
        }
        
        $image = new Imagick($src);
        if ($crop !== false) {
            $image->cropThumbnailImage($width, $height);
        } else {
            $image->scaleimage($width, 0);
        }
        $image->setImageFormat($format);
        return $image->writeImage($dest);
    }
}