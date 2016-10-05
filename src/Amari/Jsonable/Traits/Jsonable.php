<?php

namespace Amari\Jsonable\Traits;

use Amari\Files\File;
use Amari\Files\Image;

/**
 * Class Jsonable
 * @package App\Models\Traits
 */
trait Jsonable {

	protected $jsonArray = [];
	
	public function getColumn($key) {
		foreach (static::$json as $column => $item) if (in_array($key, $item)) {
			return $column;
		}
		return false;
	}

	public function setAttribute($key, $value) {;
		if ($this->getColumn($key)) {
			return $this->setColumnAttributes([$key => $value]);
		}

		return parent::setAttribute($key, $value);
	}

	public function checkTypes($data) {
		if (is_array($data)) foreach ($data as $i => &$items) if (is_array($items)) foreach ($items as $key => &$value) if (array_key_exists('_' . $key, $items)) switch ($items['_' . $key]) {
			case 'image':
				$value = new Image($value);
				break;
			case 'file':
				$value = new File($value);
				break;
		}

		return $data;
	}

	public function getAttribute($key) {
		if ($column = $this->getColumn($key)) {
			return $this->getColumnAttribute($column, $key);
		}

		if (count($deep = array_reverse(array_map('strrev', explode('_', strrev(snake_case($key)), 2)))) > 1 && ($column = $this->getColumn($deep[0]))) {
			if ($deep[1] == 'array') {
				return json_decode($this->getColumnAttribute($column, $deep[0]), true);
			} elseif ($deep[1] == 'collection') {
				return collect($this->checkTypes(json_decode($this->getColumnAttribute($column, $deep[0]), true)))->sortBy('sort');
			}
		}

		return parent::getAttribute($key);
	}


	protected function loadColumn($column, $force = false) {
		return array_key_exists($column, $this->jsonArray) && !$force ? $this->jsonArray[$column] : (($res = json_decode(isset($this->attributes[$column]) ? $this->attributes[$column] : '', true)) ? $this->jsonArray[$column] = $res : []);
	}

	protected function getColumnAttribute($column, $key) {
		return array_key_exists($key, $this->loadColumn($column)) ? $this->jsonArray[$column][$key] : null;
	}

	public function setColumnAttributes(Array $params) {
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

	function loadColumns() {
		foreach (static::$json as $column => $item) {
			$this->loadColumn($column);
		}
		return $this->jsonArray;
	}
}