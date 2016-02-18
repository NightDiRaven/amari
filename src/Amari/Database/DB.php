<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 17.02.2016
 * Time: 14:06
 */

namespace Amari\Database;

use \PDO;

class DB {

	protected static $_instance;
	protected $_db;
	protected $connections;
	protected $options;
	protected $driver;
	protected $ready = false;

	protected function __construct() {}

	protected function __clone() { }

	public static function instance($params = []){
		if(!(self::$_instance instanceof self))
			self::$_instance = new DB($params);
		return self::$_instance;
	}

	protected function prepareConnect(){
		$this->connections = config('database.connections');
		$this->driver = config('database.driver');
		if(array_key_exists($this->driver, $this->connections)){
			$this->options = $this->connections[$this->driver];
			$this->ready = true;
		}
	}

	public function connect(){
		$connectMethod = 'connect'.ucfirst($this->driver);
		if(method_exists($this,$connectMethod)){
			$this->$connectMethod();
		} else throw new Exception('No driver support:'.$this->driver);
	}

	public function check(){
		if(!$this->_db){
			if(!$this->ready)
				$this->prepareConnect();
			$this->connect();
		}
		return true;
	}

	protected function connectSqlite(){
		if(!($this->_db = new PDO('sqlite:'.$this->options['database'])))
			throw new Exception('Cannot connect to database'.$this->driver);
	}

	protected function connectPgsql(){
		$params = 'pgsql:dbname='.$this->options['database'].' host='.$this->options['host'].' user='.$this->options['username'].' password='.$this->options['password'];
		if(!($this->_db = new PDO($params)))
			throw new Exception('Cannot connect to database'.$this->driver);
	}


	public function pdo(){
		if($this->check())
			return $this->_db;
	}

	public function create($table, $collback){
		return Blueprint\Schema::create($table,$collback);
	}

	public function drop($table){
		return Blueprint\Schema::drop($table);
	}
}