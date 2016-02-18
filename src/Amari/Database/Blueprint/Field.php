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
		return $this->prove('nullable', $flag);
	}

	public function primary(){
		$this->prove('primary', true);
		$this->prove('index', false);
		$this->prove('unique', false);
		$this->prove('nullable', false);
		return $this;
	}


	public function references($field){
		$this->prove('index', true);
		$references = new Reference($field);
		$this->prove('references', $references);
		return $references;
	}

	public function index(){
		$this->prove('index', true);
		return $this;
	}

	public function increment(){
		$this->prove('auto', true);
		return $this;
	}

	public function unique(){
		$this->prove('unique', true);
		return $this;
	}

	public function defaults($value){
		$this->prove('default', $value?$value:'false');
		return $this;
	}

	public function getRaw() {
		return $this->raw;
	}
}