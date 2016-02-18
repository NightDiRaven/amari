<pre>
<?php

error_reporting(-1);
ini_set("display_errors", 1);

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

Amari\App::instance()->initiate(function(){
	Amari\Database\Blueprint\Schema::drop('example');

	Amari\Database\Blueprint\Schema::create('example', function(\Amari\Database\Blueprint\Blueprint $table){
		$table->integer('id')->primary();
		$table->varchar('address',255)->nullable();
		$table->text('description');
		$table->json('descript')->unique();
		$table->varchar('des',200)->unique();

		$table->index(['description', 'des']);
		$table->unique(['address', 'id','des']);
	});


	var_dump(123);

});

