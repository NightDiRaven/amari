<?php

namespace Amari\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model {

	protected $table = 'langs';
	public $timestamps = false;
	public $fillable = ['title', 'code', 'main'];

	public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null) {
		$instance = with(new $class());
		$table = $instance->getTable();
		return parent::belongsToMany($class, SchemaLanguage::formatName($table), 'lang_id', SchemaLanguage::formatForeign($table))
					 ->withPivot($table->translatable());
	}

	public static function addMainLocale() {
		return self::firstOrCreate([
			'title' => config('langs.origin_locale_name') ?? config('app.locale'),
			'code'  => config('app.locale'),
			'main'  => true,
		]);
	}

	public static function boot() {
		parent::boot();

		static::saving(function (Model $item) {
			$attrs = $item->getDirty();
			if (isset($attrs['code'])) {
				$item->attributes['code'] = strtolower(str_slug($item->attributes['code']));
			}
			if (isset($attrs['main']) and $attrs['main']) {
				foreach ((($item->exists) ? static::where('id', '<>', $item->id)->get() : static::all()) as $other) {
					$other->setAttribute('main', false)->save();
				}
			}
		});
	}
}
