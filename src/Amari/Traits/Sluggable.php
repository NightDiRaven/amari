<?php

namespace Amari\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Class Sluggable
 * @method $this|null bySlug(string $slug)
 * @method $this|null slugOrFail(string $slug)
 * @method $this slugOrCreate(string $slug, array | null $params)
 * @package App\Models\Sluggable\Traits
 */
trait Sluggable
{

    # Helper scopes

    /**
     * @param Builder $q
     * @param string $slug
     *
     * @return Builder
     */
    public static function scopeBySlug($q, string $slug): Builder
    {
        return $q->where(static::getSlugField(), $slug);
    }

    /**
     * @param Builder $q
     * @param string $slug
     *
     * @return $this|null
     */
    public static function scopeSlugOrFail($q, string $slug)
    {
        return $q->where(static::getSlugField(), $slug)->first() ?? abort(404);
    }

    /**
     * @param Builder $q
     * @param string $slug
     * @param array|null $params
     *
     * @return $this|null
     */
    public static function scopeSlugOrCreate($q, string $slug, array $params = [])
    {
        return $q->where(static::getSlugField(), $slug)->first() ?? static::create([static::getSlugField() => $slug] + $params);
    }

    # Trait

    /**
     * Get slug source field
     *
     * @return string
     */
    public static function getSlugSource(): string
    {
        return (property_exists(static::class, 'slugSource')) ? static::$slugSource : 'title';
    }

    /**
     * Get slug field
     *
     * @return string
     */
    public static function getSlugField(): string
    {
        return (property_exists(static::class, 'slugField')) ? static::$slugField : 'slug';
    }

    /**
     * Call it in static boot method before save
     */
    public function generateSlug()
    {
        $attrs = $this->getDirty();
        $slugSource = static::getSlugSource();
        $slugField = static::getSlugField();

        // process only changed slugs or new items without provided slug
        if ($this->exists and !isset($attrs[$slugField])) return;

        // generate slug from source if it was not provided
        $slug = Str::slug(empty($attrs[$slugField]) ? $this->attributes[$slugSource] : $attrs[$slugField]);

        // check unique
        $original = $slug ? $slug : 'none';
        $num = 0;
        while (true) {
            $q = static::where($slugField, $slug);
            // exclude item itself from checking
            if ($this->exists) $q->where($this->primaryKey, '!=', $this->attributes[$this->primaryKey]);
            if (!$q->exists()) break;

            // append next number to slug (until slug becomes unique)
            $slug = $original . '-' . (++$num);
        }

        $this->attributes[$slugField] = $slug;
    }

}