<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 17.02.2016
 * Time: 18:23
 */

namespace Amari\Database\Migrations;


abstract class Migration implements IMigration{
	public function check(){

	}

	abstract public function up();
	abstract public function down();
}