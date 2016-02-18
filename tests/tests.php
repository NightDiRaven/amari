<pre>
<?php

error_reporting(-1);
ini_set("display_errors", 1);

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

Amari\App::instance()->initiate(function(){
	Amari\Database\Blueprint\Schema::drop('tag_groups');
	Amari\Database\Blueprint\Schema::drop('tags');
	Amari\Database\Blueprint\Schema::drop('migrations');

	db()->create('tag_groups', function ($table) {
		$table->increments('id');
		$table->string('title');
	});

	db()->create('tags', function ($table) {
		$table->increments('id');
		$table->string('name');

		$table->integer('group_id')->references('id')->on('tag_groups')->onDelete('cascade');
	});

	db()->create('migrations', function($table){
		$table->increments('id');
		$table->string('filename');
		$table->boolean('run')->defaults(false);
	});

	var_dump(123);

});

