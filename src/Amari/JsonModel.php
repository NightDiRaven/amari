<?php

namespace Amari;

use Amari\Contracts\JsonableContract;
use Amari\Files\File;
use Amari\Files\Image;
use Amari\Traits\Jsonable;
use Amari\Translatable\Contracts\TranslatableContract;
use Amari\Translatable\Services\Locale;
use Amari\Translatable\Traits\Translatable;

abstract class JsonModel extends Model implements JsonableContract, TranslatableContract
{
    use Jsonable, Translatable {
        Jsonable::setAttribute as jsonSetAttribute;
        Jsonable::getAttribute as jsonGetAttribute;
        Translatable::setDirtyAttribute as translateSetAttribute;
        Translatable::getTransAttribute as translateGetAttribute;
    }

    protected function getJsonCasts(): array
	{
		return [
			'image' => function ($value) {
				return new Image($value);
			},
			'file'  => function ($value) {
				return new File($value);
			},
			'lang'  => function ($value,$values,$key) {
				$fieldName = $key.'_'.Locale::instance()->prefix();
				return array_key_exists($fieldName ,$values) ? $values[$fieldName] : $value ;
			},
			'lang_image'  => function ($value,$values,$key) {
				$fieldName = $key.'_'.Locale::instance()->prefix();
				$val = (array_key_exists($fieldName ,$values) && $values[$fieldName]) ? $values[$fieldName] :  $values[$key];
				return  new Image($val);
			},
		];
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
