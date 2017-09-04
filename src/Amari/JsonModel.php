<?php

namespace Amari;

use Amari\Contracts\JsonableContract;
use Amari\Traits\Jsonable;
use Amari\Translatable\Contracts\TranslatableContract;
use Amari\Translatable\Traits\Translatable;

abstract class JsonModel extends Model implements JsonableContract, TranslatableContract
{
    use Jsonable, Translatable {
        Jsonable::setAttribute as jsonSetAttribute;
        Jsonable::getAttribute as jsonGetAttribute;
        Translatable::setDirtyAttribute as translateSetAttribute;
        Translatable::getTransAttribute as translateGetAttribute;
    }

    public function getAttribute($key)
    {
        return $this->translateGetAttribute($key) ?? $this->jsonGetAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        return $this->translateSetAttribute($key, $value) ?? $this->jsonSetAttribute($key, $value);
    }
}
