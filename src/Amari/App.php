<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 12:15
 */

namespace Amari;


class App {

	protected static $_instance;

	protected function __construct() {
		$this->helpers();
	}

	protected function __clone() { }

	public static function instance($params = null){
		if(!(self::$_instance instanceof self))
			self::$_instance = new App($params);
		return self::$_instance;
	}

	public function helpers(){
		Helpers\Helper::initiate();
	}

	public function initiate($body){
		return $body();
	}
}