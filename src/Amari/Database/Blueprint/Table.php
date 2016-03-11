<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 15:41
 */

namespace Amari\Database\Blueprint;

use  Amari\Database\DB;

class Table {

	public static function instance($name) {
		$tableClass = 'Amari\Database\Blueprint\Table\\'.ucfirst(DB::instance()->driver()) . 'Table';

		if (class_exists($tableClass)) {
			$classInstance = new $tableClass($name);
			if (in_array(Table\ITable::class, class_implements($classInstance)))
				return $classInstance;
		}
		return false;
	}
}