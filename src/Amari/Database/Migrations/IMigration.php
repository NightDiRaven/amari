<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 17.02.2016
 * Time: 16:25
 */

namespace Amari\Database\Migrations;


interface IMigration {

	public function up();
	public function down();
}