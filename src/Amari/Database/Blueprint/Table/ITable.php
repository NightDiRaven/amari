<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 19.02.2016
 * Time: 14:12
 */

namespace Amari\Database\Blueprint\Table;

interface ITable {

	public function title();
	public function select($fields);
	public function insert(Array $fields);
	public function delete();
	public function exists();
	public function drop();

}