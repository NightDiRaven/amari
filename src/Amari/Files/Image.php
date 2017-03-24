<?php

namespace Amari\Files;

class Image extends File
{
    const TEMPLATE_INFO = '.:type, :widthx:height';

    /**
     * @return string
     */
    public function info(): string
    {
        if ($filename = $this->resolvePath()) {
            list($width, $height) = getimagesize($filename);

            return strtr(static::TEMPLATE_INFO, [
                ':type'   => \File::extension($filename),
                ':width'  => $width,
                ':height' => $height,
            ]);
        } else {
            return '';
        }
    }

    /**
     * @param $template
     *
     * @return string|null
     */
    public function thumbnail(string $template = 'original'): string
    {
        if ($this->exists() or $this->tmpExists()) {
            return route('imagecache', [
                $template,
                $this->getFilename(),
            ]);
        } else {
            return '';
        }
    }

    protected static function getUploadPath(): string
    {
        return config('sleeping_owl.imageUploadDirectory', 'images/uploads');
    }
}
