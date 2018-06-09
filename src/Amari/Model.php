<?php

namespace Amari;

use Amari\Contracts\SluggableContract;
use Amari\Files\File;
use Amari\Files\Image;
use Amari\Translatable\Contracts\TranslatableContract;
use Illuminate\Support\Collection;

/**
 * Class Model.
 *
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    public static function boot()
    {
        parent::boot();

        static::saving(function (Model $item) {
            if (!$item->isDirty() and ($item instanceof TranslatableContract)) {
                $item->saveLangs();
            } elseif ($item instanceof SluggableContract) {
                $item->generateSlug();
            }
        });
        if (\in_array(TranslatableContract::class, class_implements(static::class), true)) {
            static::saved(function (Model $item) {
                $item->saveLangs();
            });
        }
    }

    public function getImage($name)
    {
        return new Image($this->$name);
    }

    public function backgroundImage($image = null, $thumb = 'original', $clases = '', $nophoto = 'no-photo', $exists = false)
    {
        if ($image instanceof Collection) {
            $exists = false;
            foreach ($image as $img) {
                $img = $img['image'];
                if ($img->exists() or $img->tmpExists()) {
                    $image = $img;
                    $exists = true;
                    break;
                }
            }
        } elseif (!($image instanceof Image) and ($image instanceof File)) {
            $exists = false;
        } else {
            /** @var Image $image */
            $exists = ($image and ($exists = with($image = ($image instanceof Image) ? $image : new Image($this->$image))->exists()));
            if (!$exists and $image->tmpExists()) {
                $image->save();
                $exists = true;
            }
        }

        return str_replace(['_b', '_c'], [
            '_b' => $exists ? $image->thumbnail($thumb) : '/img/no_foto.svg',
            '_c' => ($clases or !$exists) ? $clases.($exists ? '' : ' '.$nophoto) : '',
        ], $exists ? 'style="background-image: url(_b)" class="_c"' : 'class="_c"');
    }

    public function getImages(string $field): array
    {
        $images = json_decode($this->{$field}, true);
        if (\is_array($images)) {
            foreach ($images as &$image) {
                $image = new Image($image['file']);
                if (!$image->exists()) {
                    unset($image);
                }
            }
        } else {
            $images = [];
        }

        return $images;
    }
}
