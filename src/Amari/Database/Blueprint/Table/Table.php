<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 15:41
 */

namespace Amari\Database\Blueprint\Table;

use Amari\Database\Blueprint\Schema;
use Amari\Database\Blueprint\Table\Query\Query;

abstract class Table implements ITable {

	protected $title;

	public function __construct($name) {
		$this->title = $name;
	}

	public function title(){
		return $this->title();
	}

	public function select($fields = []){
		$query = new Query($this);
		return $query->select($fields);
	}

	public function insert(Array $fields){
		$query = new Query($this);
		return $query->insert($fields);
	}

	public function delete(){
		$query = new Query($this);
		return $query->delete();
	}

	abstract public function exists();

	public function drop(){
		Schema::drop($this->title());
	}
}