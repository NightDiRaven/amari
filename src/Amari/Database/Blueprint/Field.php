<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 15:49
 */

namespace Amari\Database\Blueprint;


class Field {

	protected $raw = [];

	protected function prove($key, $value){
		if(!isset($this->raw[$key])) $this->raw[$key] = $value;
	}

	public function __construct($name, $type = 'string', $opt = null){
		$this->prove('name',$name);
		$this->prove('type',$type);
		if($opt !== null)
			$this->prove('opt',$opt);
		return $this;
	}

	public function nullable($flag = true){
		$this->prove('nullable', $flag);
	}

	public function primary(){
		$this->prove('primary', true);
		$this->prove('index', false);
		$this->prove('unique', false);
		$this->prove('nullable', false);
	}

	public function index(){
		$this->prove('index', true);
	}

	public function increment(){
		$this->prove('auto', true);
	}

	public function unique(){
		$this->prove('unique', true);
	}

	public function getRaw() {
		return $this->raw;
	}
}