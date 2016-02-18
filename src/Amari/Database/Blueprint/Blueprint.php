<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 15:41
 */

namespace Amari\Database\Blueprint;


class Blueprint {

	protected $fields = [];
	protected $idxs = [];
	protected $uniq = [];

	public function __call($name, $arguments) {
		if (!isset($arguments[0])) return;
		return $this->fields[$arguments[0]] = new Field($arguments[0], $name, isset($arguments[1]) ? $arguments[1] : null);
	}

	public function getFields() {
		return $this->fields;
	}

	public function index(Array $fields){
		$this->idxs[] = $fields;
	}

	public function increments($field){
		return $this->integer($field)->primary();
	}

	public function string($field,$length = 255){
		return $this->varchar($field, $length);
	}

	public function unique(Array $fields){
		$this->uniq[] = $fields;
	}

	public function getIdxs() {
		return $this->idxs;
	}

	public function getUniq() {
		return $this->uniq;
	}

}