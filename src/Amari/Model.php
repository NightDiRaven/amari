<?php

namespace Amari;

use Amari\Contracts\SluggableContract;
use Amari\Translatable\Contracts\TranslatableContract;
use Illuminate\Support\Collection;

/**
 * Class Model.
 *
 * @method \Illuminate\Database\Query\Builder whereIn($key, array $hash)
 * @method \Illuminate\Database\Eloquent\Collection get()
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
        if (in_array(TranslatableContract::class, class_implements(static::class), true)) {
            static::saved(function (TranslatableContract $item) {
                $item->saveLangs();
            });
        }
    }
}
