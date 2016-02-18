<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 18:01
 */

namespace Amari\Database\Blueprint;


class Reference {
	public $field;
	public $on;
	public $on_delete = false;

	public function __construct($field) {
		$this->field = $field;
		return $this;
	}

	public function on($on) {
		$this->on = $on;
		return $this;
	}

	public function onDelete($on_delete) {
		$this->on_delete = $on_delete;
		return $this;
	}

	public function full(){
		return $this->field && $this->on;
	}
}