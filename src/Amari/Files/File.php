<?php
namespace Amari\Files;

use Illuminate\Support\Facades\File as IlluminateFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class File
{

    const TEMPLATE_INFO = '.:type, :size';

    /**
     * @var string
     */

    protected $filename;

    function __construct(string $filename)
    {
        $this->filename = str_replace(static::getUploadPath() . '/', '', $filename);
    }

    public function thumbnail(string $template = 'original'): string
    {
        return '';
    }

    public function exists(): bool
    {
        return is_file($this->realPath());
    }

    public function tmpExists(): bool
    {
        return is_file($this->tmpRealPath());
    }

    public function link(): string
    {
        return url()->asset($this->path());
    }

    public function path(): string
    {
        return static::getUploadPath() . '/' . $this->filename;
    }

    public function tmpPath(): string
    {
        return static::getTempPath() . '/' . $this->filename;
    }

    public function realPath(): string
    {
        return public_path($this->path());
    }

    public function tmpRealPath(): string
    {
        return public_path($this->tmpPath());
    }

    public function save(): bool
    {
        return $this->exists() or $this->tmpExists() && rename($this->tmpRealPath(), $this->realPath());
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function delete(): bool
    {
        if ($this->exists()) return IlluminateFile::delete($this->path());
        elseif ($this->tmpExists()) return IlluminateFile::delete($this->tmpPath());
        else return false;
    }

    public function info(): string
    {
        return ($filename = $this->resolvePath()) ? strtr(static::TEMPLATE_INFO, [
            ':type' => IlluminateFile::extension($filename),
            ':size' => round(IlluminateFile::size($filename) / 1000, 2) . ' Kb'
        ]) : '';
    }

    /**
     * @return bool|string
     */
    public function resolvePath()
    {
        if ($this->exists())
            return $this->realPath();
        elseif ($this->tmpExists())
            return $this->tmpRealPath();
        else return false;
    }

    public function mv(string $destination): bool
    {
        return $path = $this->resolvePath() and rename($path, $destination);
    }

    public function __toString(): string
    {
        return $this->filename;
    }

    public static function upload(UploadedFile $file, bool $saveName = false, bool $overwrite = false)
    {
        $path = static::getUploadPath() . '/';

        if ($saveName) {
            $filename = $file->getClientOriginalName();
            if (!$overwrite) {
                $count = 0;
                while (file_exists($path . $filename)) $filename = '(' . ++$count . ') ' . $file->getClientOriginalName();
            }
        } else $filename = md5(time() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();

        $file->move(static::getTempPath(), $filename);

        return new static($filename);
    }

    protected static function getUploadPath(): string
    {
        return config('sleeping_owl.filesUploadDirectory', 'files/uploads');
    }

    protected static function getTempPath(): string
    {
        return 'tmp/uploads';
    }
}