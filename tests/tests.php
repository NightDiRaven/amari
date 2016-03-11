<pre>
<?php

error_reporting(-1);
ini_set("display_errors", 1);

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use Amari\Database\Blueprint\Schema;
use Amari\Database\Blueprint\Blueprint;

Amari\App::instance()->initiate(function(){
	$statuses = [];
/*
	$statuses[] = Schema::drop('tag_groups');
	$statuses[] = Schema::drop('tags');
	$statuses[] = Schema::drop('migrations');
	$statuses[] = Schema::drop('items');
	$statuses[] = Schema::drop('item_tag');
*/

	var_dump(db()->table('test')->select('id'));
	var_dump(db()->table('test')->select(['id']));
	var_dump(db()->table('test')->select(['id'])->where('id', 234));

	/*
	$statuses[] = db()->create('migrations', function(Blueprint $table){
		$table->increments('id');
		$table->string('filename');
		$table->boolean('run')->defaults(false);
	});


	$statuses[] = db()->create('tag_groups', function (Blueprint $table) {
		$table->increments('id');
		$table->string('title');
		$table->custom('try integer not null');
	});

	$statuses[] = db()->create('tags', function (Blueprint $table) {
		$table->increments('id');
		$table->string('name');
		$table->text('description')->nullable();

		$table->integer('group_id')->references('id')->on('tag_groups')->onDelete('cascade');
	});

	$statuses[] = Schema::create('items', function (Blueprint $table) {
		$table->increments('id');
		$table->string('title');
		$table->text('description')->nullable();
		$table->string('thumb')->nullable();
		$table->jsonb('gallery')->nullable();
		$table->integer('sort')->defaults(0)->index();
		$table->string('slug')->unique();
	});


	$statuses[] = Schema::create('item_tag', function (Blueprint $table) {
		$table->integer('item_id')->references('id')->on('items')->onDelete('cascade');
		$table->integer('tag_id')->references('id')->on('tags')->onDelete('cascade');
		$table->unique(['item_id', 'tag_id']);
	});
*/

	var_dump($statuses);

});

