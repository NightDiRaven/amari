<?php

namespace Amari\Traits;

use Amari\Files\File;
use Amari\Files\Image;
use Illuminate\Support\Str;

/**
 * Class Jsonable
 *
 * This trait make simple one level json attributes declaration to your models. Its kinda shit but works perfectly.
 *
 * Usage:
 *
 *
 *
 *
 * @package App\Models\Traits
 */
trait Jsonable
{

    /** @var array All fetched columns here (lazy load json_decode) */
    protected $jsonArray = [];

    /**
     * Get save db field for store $key attribute
     *
     * @param $key
     * @return bool|string
     */
    protected function getColumn($key)
    {
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
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        ;
        if ($this->getColumn($key)) {
            return $this->setJsonAttributes([$key => $value]);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * After parse for collection attribute transform fields to object if underscore filed value exists;
     *
     * For example, you have in your json {
     *      title: 'Awesome monkey code',
     *      cover: '/static/awesome_image.jpg',
     *      _cover: 'image'
     * }
     *
     * if so, when you get attributeCollection _cover will be unset and cover string replace by this method rule object
     *
     * If you don't need cast, replace with simple return $data;
     *
     * @param $data
     * @return mixed
     */
    public function castTypes($data)
    {
        if (is_array($data)) foreach ($data as $i => &$items) if (is_array($items)) foreach ($items as $key => &$value)
            if (array_key_exists('_' . $key, $items)) switch ($items['_' . $key]) {
                case 'image':
                    $value = new Image($value);
                    break;
                case 'file':
                    $value = new File($value);
                    break;
            }

        return $data;
    }

    /**
     * Override default model event, and declare fast collection and array one level accessors for json
     *
     * @param $key
     * @return mixed|null|static
     */
    public function getAttribute($key)
    {
        if ($column = $this->getColumn($key)) {
            return $this->getJsonAttributeBy($column, $key);
        }

        /**
         * Find attributes ends with Array or Collection
         */
        foreach ([4 => 'Cast', 5 => 'Array', 10 => 'Collection'] as $len => $needle) {
            $keyLength = strlen($key);
            if ($keyLength > $len and substr($key, -$len) === $needle) {
                if ($column = $this->getColumn($jsonKey = snake_case(substr($key, 0, $keyLength - $len)))) {
                    $data = json_decode($this->getJsonAttributeBy($column, $jsonKey), true);
                    if ($needle == 'Array') {
                        return $data;
                    } elseif ($needle == 'Cast') {
                        return $this->castTypes($data);
                    } elseif ($needle == 'Collection') {
                        return collect($this->castTypes($data))->sortBy('sort');
                    }
                }
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * json_decode attribute and return value
     *
     * @param $column
     * @param bool $force
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
     * Get json attribute
     *
     * @param $column
     * @param $key
     * @return null
     */
    protected function getJsonAttributeBy($column, $key)
    {
        return array_key_exists($key, $this->loadColumn($column)) ? $this->jsonArray[$column][$key] : null;
    }

    /**
     * Set json attributes in key => value format
     *
     * @param array $params
     * @return $this
     */
    public function setJsonAttributes(Array $params)
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
     * Json decode all json fields
     *
     * @return array
     */
    function loadColumns()
    {
        foreach (static::$json as $column => $item) {
            $this->loadColumn($column);
        }
        return $this->jsonArray;
    }
}