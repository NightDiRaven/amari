<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 19.02.2016
 * Time: 14:47
 */

namespace Amari\Database\Blueprint\Table\Query;


class Query {
	protected $table;
	protected $type = null;

	protected $fields = [
		'select' => [],
		'insert' => [],
		'delete' => [],
		'update' => [],
	];

	public function __construct($table) {
		$this->table = $table;
	}

	public function __call($name, $arguments) {
		if (!array_key_exists($name, $this->fields)) return;
		if (isset($arguments[0]) && is_array($arguments[0])) $arguments = $arguments[0];
		$this->fields[$name] = array_merge($this->fields[$name], $arguments);
		$this->prove($name);
		return $this;
	}

	protected function prove($value){
		if(!($this->type)) $this->type = $value;
	}
}