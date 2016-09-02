<?php

namespace Amari;

use Amari\Files\Image;
use Amari\Sluggable\Contracts\SluggableContract;
use Amari\Translatable\Contracts\TranslatableContract;

/**
 * Class Model
 *
 * @method Builder whereIn($key, array $hash)
 * @method Collection get()
 * @package Amari
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model {

	public static function boot() {
		parent::boot();

		static::saving(function (Model $item) { if ($item instanceof SluggableContract) $item->generateSlug(); });
		static::saved(function (Model $item) { if ($item instanceof TranslatableContract) $item->saveLangs(); });
	}

	public function getImage($name) {
		return new Image($this->$name);
	}

	public function backgroundImage($image = null, $thumb = 'original', $clases = '', $nophoto = 'no-photo', $exists = false) {
		return str_replace(['_b','_c'],[
			'_b' => ($image and ($exists = with($image = ($image instanceof Image)? $image : new Image($this->$image))->exists())) ? $image->thumbnail($thumb) : '/img/no_foto.svg',
			'_c' => ($clases or !$exists) ? $clases.($exists?'':' '.$nophoto):''
		], $exists?'style="background-image: url(_b)" class="_c"':'class="_c"');
	}

	public function getImages(string $field) : array {
		$images = json_decode($this->{$field}, true);
		if (is_array($images)) {
			foreach ($images as &$image) {
				$image = new Image($image['file']);
				if (!$image->exists()) unset($image);
			}
		} else $images = [];
		return $images;
	}
}