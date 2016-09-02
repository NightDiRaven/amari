<?php

namespace Amari\Sluggable\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class Sluggable
 * @method $this|null bySlug(string $slug)
 * @method $this|null slugOrFail(string $slug)
 * @method $this slugOrCreate(string $slug, array|null $params)
 * @package App\Models\Sluggable\Traits
 */
trait Sluggable {

	# Helper scopes

	/**
	 * @param Builder $q
	 * @param string  $slug
	 *
	 * @return Builder
	 */
	public static function scopeBySlug($q, string $slug) : Builder {
		return $q->where('slug', $slug);
	}

	/**
	 * @param Builder $q
	 * @param string  $slug
	 *
	 * @return Builder
	 */
	public static function scopeSlugOrFail($q, string $slug) : Builder {
		return $q->where('slug', $slug)->first() ?? abort(404);
	}

	/**
	 * @param Builder    $q
	 * @param string     $slug
	 * @param array|null $params
	 *
	 * @return $this|null
	 */
	public static function scopeSlugOrCreate($q, string $slug, array $params = []) {
		return $q->where('slug', $slug)->first() ?? static::create(['slug' => $slug] + $params);
	}

	# Trait

	public function getSlugSource() : string {
		return (property_exists($this, 'slugSource')) ? $this->slugSource : 'title';
	}

	public function getSlugField() : string {
		return (property_exists($this, 'slugField')) ? $this->slugField : 'slug';
	}

	/**
	 * Call it in static boot method before save
	 */
	protected function generateSlug() {
		$attrs = $this->getDirty();

		// process only changed slugs or new items without provided slug
		if ($this->exists and !isset($attrs[$this->getSlugField()])) return;

		// generate slug from source if it was not provided
		$slug = str_slug(empty($attrs[$this->getSlugField()]) ? $this->attributes[$this->getSlugSource()] : $attrs[$this->getSlugField()]);

		// check unique
		$original = $slug;
		$num = 0;
		while (true) {
			$q = static::where('slug', $slug);
			// exclude item itself from checking
			if ($this->exists) $q->where($this->primaryKey, '!=', $this->attributes[$this->primaryKey]);
			if (!$q->exists()) break;

			// append next number to slug (until slug becomes unique)
			$slug = $original . '-' . (++$num);
		}

		$this->attributes[$this->getSlugField()] = $slug;
	}

}