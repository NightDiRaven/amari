<?php

namespace Amari\Traits;

use Amari\Contracts\JsonCastContract;
use Amari\Files\File;
use Amari\Files\Image;
use Illuminate\Support\Collection;

/**
 * Class Jsonable
 *
 * This trait make simple one level json attributes declaration to your models. Its kinda shit but works perfectly.
 *
 * @package App\Models\Traits
 */
trait Jsonable {

	/** @var array All fetched columns here (lazy load json_decode) */
	protected $jsonArray = [];

	/**
	 * Get save db field for store $key attribute
	 *
	 * @param $key
	 *
	 * @return bool|string
	 */
	protected function getColumn($key) {
		foreach (static::$json as $column => $jsonItems) if (in_array($key, $jsonItems)) {
			return $column;
		}

		return false;
	}

	/**
	 * Override default model event, but if you want you can generate for your models specific methods, for example
	 *
	 * protected $json = [
	 *      'body' => [
	 *          'testField1',
	 *          'testField2',
	 *      ]
	 * ];
	 *
	 * public function setTestField1Attribute($value){
	 *      $this->setColumnAttribute(['testField1', $value]);
	 * }
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function setAttribute($key, $value) {
		if ($this->getColumn($key = snake_case($key))) {
			return $this->setJsonAttributes([$key => $value]);
		}

		return parent::setAttribute($key, $value);
	}

	/**
	 * Override default model event fo easy access
	 *
	 * @param $key
	 *
	 * @return mixed|null|static
	 */
	public function getAttribute($key) {
		if ($column = $this->getColumn($jsonKey = snake_case($key))) {
			return $this->getJsonAttributeBy($column, $jsonKey);
		}

		return parent::getAttribute($key);
	}

	/**
	 * json_decode attribute and return value
	 *
	 * @param      $column
	 * @param bool $force
	 *
	 * @return array|mixed
	 */
	protected function loadColumn($column, $force = false) {
		return array_key_exists($column, $this->jsonArray) && !$force
			? $this->jsonArray[$column]
			: (($res = isset($this->attributes[$column]) ? json_decode($this->attributes[$column], true) : false)
				? $this->jsonArray[$column] = $res : []);
	}

	/**
	 * Get json attribute
	 *
	 * @param $column
	 * @param $key
	 *
	 * @return null
	 */
	protected function getJsonAttributeBy($column, $key) {
		return array_key_exists($key, $this->loadColumn($column)) ? $this->jsonArray[$column][$key] : null;
	}

	/**
	 * Set json attributes in key => value format
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	public function setJsonAttributes(Array $params) {
		$prepare = [];

		foreach ($params as $key => $param) {
			if ($column = $this->getColumn($key)) {
				if (!isset($prepare[$column])) {
					$prepare[$column] = $this->loadColumn($column);
				}
				$prepare[$column][$key] = $param;
			}
		}
		foreach ($prepare as $key => $item) {
			$this->attributes[$key] = json_encode($item);
			$this->loadColumn($key, true);
		}

		return $this;
	}

	/**
	 * Json decode all json fields
	 *
	 * @return array
	 */
	function loadColumns() {
		foreach (static::$json as $column => $item) {
			$this->loadColumn($column);
		}

		return $this->jsonArray;
	}

	/**
	 * Get json casts array
	 *
	 * @return array
	 */
	protected function getJsonCasts(): array {
		if (property_exists(static::class, 'jsonCasts')) return static::$jsonCasts;
		else return [
			'image' => function ($value) { return new Image($value); },
			'file'  => function ($value) { return new File($value); }
		];
	}

	/**
	 * Initialize json field cast too other objects
	 *
	 * @param $field
	 *
	 * @return JsonCastContract
	 */
	public function jsonCast($field) {

		return new class($this->getAttribute($field), $this->getJsonCasts()) implements JsonCastContract {
			public $attribute;

			public function __construct($attribute, $casts) {
				$this->attribute = is_array($attribute) ? $attribute : json_decode($attribute . true);
				$this->casts = $casts;
			}

			protected function castRecursive(&$values, &$casts) {
				if (is_array($values))
					foreach ($values as $key => &$value) {
						if (isset($casts[$key]))
							$value = $casts[$key]($value);
						elseif (is_array($value)) $this->castRecursive($value, $casts);
					}
			}

			public function cast(array $fieldFormat = []) {
				$casts = $this->casts;
				foreach ($fieldFormat as $field => $cast) {
					if (array_key_exists($cast, $casts)) $casts[$field] = $casts[$cast];
					elseif (is_callable($cast)) $casts[$field] = $cast;
				}
				$this->castRecursive($this->attribute, $casts);

				return $this;
			}

			public function toArray(): array {
				return is_array($this->attribute) ? $this->attribute:[$this->attribute];
			}

			public function castArray(array $fieldFormat = []): array {
				return $this->cast($fieldFormat)->toArray();
			}

			public function toCollection(): Collection {
				return collect($this->attribute);
			}

			public function __get($name) {
				if (isset($this->attributes[$name]))
					return $this->attributes[$name];
			}
		};
	}
}