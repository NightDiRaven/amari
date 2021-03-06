<?php

namespace Amari\Traits;

use Amari\Contracts\JsonCastContract;
use Amari\Files\File;
use Amari\Files\Image;
use Amari\Translatable\Contracts\JsonableContract;
use Illuminate\Support\Collection;

/**
 * Class Jsonable.
 *
 * This trait make simple one level json attributes declaration to your models. Its kinda shit but works perfectly.
 */
trait Jsonable
{
    /** @var array Json column declarations */
    protected static $json = [];

    /** @var array All fetched columns here (lazy load json_decode) */
    protected $jsonArray = [];

    /** @var array|bool Custom model json configuration */
    public $jsonConfig = false;

    /**
     * Get save db field for store $key attribute.
     *
     * @param $key
     *
     * @return bool|string
     */
    protected function getColumn($key)
    {
        foreach ($this->getJson() as $column => $jsonItems) {
            if (in_array($key, $jsonItems)) {
                return $column;
            }
        }

        return false;
    }

    /**
     * Get configuration of json model.
     *
     * @return array
     */
    public function getJson()
    {
        return $this->jsonConfig ? $this->jsonConfig : static::$json;
    }

    /**
     * @param array|bool $jsonConfig
     */
    public function setJson($jsonConfig)
    {
        $this->jsonConfig = $jsonConfig;
    }

    /**
     * Set configuration of jsonModel.
     *
     * @param $class
     */
    public static function changeJson($class)
    {
        if (is_array($class)) {
            static::$json = $class;
        } elseif (in_array(JsonableContract::class, class_implements($class))) {
            static::$json = (is_object($class) and $class->jsonConfig) ? $class->jsonConfig : $class::$json;
        }
    }

    /**
     * Very dangerous command, you can flush your json data structure.
     *
     * @param $class
     *
     * @return $this
     */
    public function morphJsonTo($class)
    {
        if (in_array(JsonableContract::class, class_implements($class))) {
            $this->jsonConfig = (is_object($class) and $class->jsonConfig) ? $class->jsonConfig : $class::$json;
        }

        return $this;
    }

    /**
     * Change json format by merging config with another jsonable.
     *
     * @param $class
     *
     * @return $this
     */
    public function mergeJsonWith($class)
    {
        if (in_array(JsonableContract::class, class_implements($class))) {
            $this->jsonConfig = array_merge_recursive($this->getJson(),
                (is_object($class) and $class->jsonConfig) ? $class->jsonConfig : $class::$json);
        }

        return $this;
    }

    /**
     * Override default model event, but if you want you can generate for your models specific methods, for example.
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
    public function setAttribute($key, $value)
    {
        if ($this->getColumn($key = snake_case($key))) {
            return $this->setJsonAttributes([$key => $value]);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override default model event fo easy access.
     *
     * @param $key
     *
     * @return mixed|null|static
     */
    public function getAttribute($key)
    {
        if ($column = $this->getColumn($jsonKey = snake_case($key))) {
            return $this->getJsonAttributeBy($column, $jsonKey);
        }

        return parent::getAttribute($key);
    }

    /**
     * json_decode attribute and return value.
     *
     * @param      $column
     * @param bool $force
     *
     * @return array|mixed
     */
    protected function loadColumn($column, $force = false)
    {
        return array_key_exists($column, $this->jsonArray) && !$force
            ? $this->jsonArray[$column]
            : (($res = isset($this->attributes[$column]) ? json_decode($this->attributes[$column], true) : false)
                ? $this->jsonArray[$column] = $res : []);
    }

    /**
     * Get json attribute.
     *
     * @param $column
     * @param $key
     *
     * @return null
     */
    protected function getJsonAttributeBy($column, $key)
    {
        return array_key_exists($key, $this->loadColumn($column)) ? $this->jsonArray[$column][$key] : null;
    }

    /**
     * Set json attributes in key => value format.
     *
     * @param array $params
     *
     * @return $this
     */
    public function setJsonAttributes(array $params)
    {
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
     * Json decode all json fields.
     *
     * @return array
     */
    public function loadColumns()
    {
        foreach ($this->getJson() as $column => $item) {
            $this->loadColumn($column);
        }

        return $this->jsonArray;
    }

    /**
     * Get json casts array.
     *
     * @return array
     */
    protected function getJsonCasts(): array
    {
        if (property_exists(static::class, 'jsonCasts')) {
            return static::$jsonCasts;
        } else {
            return [
            'image' => function ($value) {
                return new Image($value);
            },
            'file'  => function ($value) {
                return new File($value);
            },
        ];
        }
    }

    /**
     * Initialize json field cast to other objects.
     *
     * @param string $field
     *
     * @return JsonCastContract
     */
    public function jsonCast(string $field): JsonCastContract
    {
        return new class($this->getAttribute($field), $this->getJsonCasts()) implements JsonCastContract {
            public $attribute;

            public function __construct($attribute, $casts)
            {
                $this->attribute = is_array($attribute) ? $attribute : json_decode($attribute, true);
                $this->casts = $casts;
            }

            protected function castRecursive(&$values, &$casts)
            {
                if (is_array($values)) {
                    foreach ($values as $key => &$value) {
                        if (isset($casts[$key])) {
                            $value = $casts[$key]($value, $values, $key);
                        } elseif (is_array($value)) {
                            $this->castRecursive($value, $casts);
                        }
                    }
                }
            }

            public function cast(array $fieldFormat = [])
            {
                $casts = $this->casts;
                foreach ($fieldFormat as $field => $cast) {
	                if (is_callable($cast)) {
		                $casts[$field] = $cast;
	                } elseif (array_key_exists($cast, $casts)) {
		                $casts[$field] = $casts[$cast];
	                }
                }
                $this->castRecursive($this->attribute, $casts);

                return $this;
            }

            public function toArray(): array
            {
                return is_array($this->attribute) ? $this->attribute : [$this->attribute];
            }

            public function castArray(array $fieldFormat = []): array
            {
                return $this->cast($fieldFormat)->toArray();
            }

            public function toCollection(): Collection
            {
                return collect($this->attribute);
            }

            public function __get($name)
            {
                if (isset($this->attributes[$name])) {
                    return $this->attributes[$name];
                }
            }
        };
    }
}
