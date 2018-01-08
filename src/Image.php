<?php

namespace Geo6;

use ErrorException;

class Image
{
    private $file = null;
    private $height;
    private $resource;
    private $type = null;
    private $sourceFile = null;
    private $tempnam = null;
    private $width;

    public function __construct(int $width, int $height)
    {
        $this->height = $height;
        $this->resource = imagecreatetruecolor($width, $height);
        $this->width = $width;
    }

    public function __destruct()
    {
        imagedestroy($this->resource);

        if (file_exists($this->tempnam)) {
            unlink($this->tempnam);
        }
    }

    public static function createFromFile(string $file)
    {
        if (file_exists($file) && is_readable($file)) {
            list($width, $height, $type) = getimagesize($file);

            $new = new self($width, $height);
            imagedestroy($new->resource);

            $new->file = $file;
            $new->type = $type;

            switch ($new->type) {
                case IMAGETYPE_BMP:
                    $new->resource = imagecreatefrombmp($file);
                    break;
                case IMAGETYPE_GIF:
                    $new->resource = imagecreatefromgif($file);
                    break;
                case IMAGETYPE_JPEG:
                    $new->resource = imagecreatefromjpeg($file);
                    break;
                case IMAGETYPE_PNG:
                    $new->resource = imagecreatefrompng($file);
                    break;
                case IMAGETYPE_WBMP:
                    $new->resource = imagecreatefromwbmp($file);
                    break;
                case IMAGETYPE_WEBP:
                    $new->resource = imagecreatefromwebp($file);
                    break;
                default:
                    throw new ErrorException(sprintf('Type "%s" is not supported.', image_type_to_mime_type($type)));
                    break;
            }
        } else {
            throw new ErrorException(sprintf('File "%s" does not exists or is not readable.', $file));
        }

        return $new;
    }

    public function thumbnail(int $maxSize)
    {
        if (max($this->width, $this->height) > $maxSize) {
            if (max($this->width, $this->height) === $this->width && $this->width > $maxSize) {
                $newWidth = $maxSize;
                $newHeight = $this->height / $this->width * $newWidth;
            } else {
                $newHeight = $maxSize;
                $newWidth = $this->width / $this->height * $newHeight;
            }

            $thumbnail = new self($newWidth, $newHeight);
            $thumbnail->type = $this->type;

            imagecopyresampled($thumbnail->resource, $this->resource, 0, 0, 0, 0, $thumbnail->width, $thumbnail->height, $this->width, $this->height);
        } else {
            $thumbnail = clone $this;
        }

        return $thumbnail;
    }

    /**
     * @link http://owl.phy.queensu.ca/~phil/exiftool/TagNames/EXIF.html
     * @link http://sylvana.net/jpegcrop/exif_orientation.html
     */
    public function EXIFRotate()
    {
        $exif = @exif_read_data($this->file);

        $rotated = new self($this->width, $this->height);
        $rotated->type = $this->type;

        imagecopy($rotated->resource, $this->resource, 0, 0, 0, 0, $rotated->width, $rotated->height);

        $height = $rotated->height;
        $width = $rotated->width;

        if (isset($exif['Orientation']) && intval($exif['Orientation']) > 1) {
            switch (intval($exif['Orientation'])) {
                case 2: // 2 = Mirror horizontal
                    imageflip($rotated->resource, IMG_FLIP_HORIZONTAL);
                    break;
                case 3: // 3 = Rotate 180 CW
                    $rotated->resource = imagerotate($rotated->resource, -180, 0);
                    break;
                case 4: // 4 = Mirror vertical
                    imageflip($rotated->resource, IMG_FLIP_VERTICAL);
                    break;
                case 5: // 5 = Mirror horizontal and rotate 270 CW
                    imageflip($rotated->resource, IMG_FLIP_HORIZONTAL);
                    $rotated->resource = imagerotate($rotated->resource, -270, 0);
                    $rotated->height = $width;
                    $rotated->width = $height;
                    break;
                case 6: // 6 = Rotate 90 CW
                    $rotated->resource = imagerotate($rotated->resource, -90, 0);
                    $rotated->height = $width;
                    $rotated->width = $height;
                    break;
                case 7: // 7 = Mirror horizontal and rotate 90 CW
                    imageflip($rotated->resource, IMG_FLIP_HORIZONTAL);
                    $rotated->resource = imagerotate($rotated->resource, -90, 0);
                    $rotated->height = $width;
                    $rotated->width = $height;
                    break;
                case 8: // 8 = Rotate 270 CW
                    $rotated->resource = imagerotate($rotated->resource, -270, 0);
                    $rotated->height = $width;
                    $rotated->width = $height;
                    break;
            }
        }

        return $rotated;
    }

    public function save(string $file)
    {
        if (file_exists($file) && is_dir($file)) {
            if (substr($file, -1) !== '/') {
                $file .= '/';
            }
            $file .= basename($this->file);
        }

        if (!is_null($this->file) && is_null($this->sourceFile)) {
            $this->sourceFile = $this->file;
        }
        $this->file = $file;

        switch ($this->type) {
            case IMG_BMP:
                $result = imagebmp($this->resource, $file);
                break;
            case IMG_GIF:
                $result = imagegif($this->resource, $file);
                break;
            case IMG_JPG:
                $result = imagejpeg($this->resource, $file);
                break;
            case IMG_PNG:
                $result = imagepng($this->resource, $file);
                break;
            case IMG_WBMP:
                $result = imagewbmp($this->resource, $file);
                break;
            case IMG_WEBP:
                $result = imagewebp($this->resource, $file);
                break;
            default:
                $result = imagepng($this->resource, $file);
                $this->type = IMAGETYPE_PNG;
                break;
        }

        return $result;
    }

    public function display()
    {
        if (is_null($this->file)) {
            $this->tempnam = tempnam(sys_get_temp_dir(), 'php_image_');
            $this->save($this->tempnam);
        }

        clearstatcache(true, $this->file);

        header('Content-Type: '.image_type_to_mime_type($this->type));
        header('Content-Length: '.filesize($this->file));
        readfile($this->file);
        exit();
    }
}
