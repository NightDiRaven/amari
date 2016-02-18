<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 16.02.2016
 * Time: 18:53
 */

namespace Amari\Console\Commands;


class Make extends Common{

	public function title(){
		return 'make';
	}

	public function lists(){
		return [
			'migration' => 'make migration'
		];
	}

	public function index($subCommand){
		$this->kernel()->say('Please type what you want create');
		foreach($class->lists() as $name=>$desc)
			$this->kernel()->send($this->color(' '.$title.':'.$name, '32'))
				->tab(strlen($name)>7?1:2)
				->string($this->color($desc, 0));
	}

	public function doMigration($args){

		if(!isset($args[2])) {
			$this->kernel()->say('Name of migration is nessesary. Example: make:migration create_new_items_table');
			return;
		}
		$safeName = trim(preg_replace('/[^a-zA-Z0-9_-]/','',$args[2]));

		$filename = date("Y-m-d_H-i-s_").$safeName.'.php';




		db()->create('migrations', function($table){
			$table->increments('id');
			$table->string('filename');
			$table->boolean('run')->default(0);
		});

		$path_to_new = app_path('database/migrations/'.$filename);

		$file_contents = file_get_contents('patterns/migration.php.pattern');
		$file_contents = str_replace("[[classname]]" ,camel_case($safeName), $file_contents);
		$file_contents = str_replace("[[tablename]]" ,'table_name', $file_contents);
		file_put_contents($path_to_new, $file_contents);

		/*db()->insert('migrations',[
			'name' => $args[2],
			'run' => 0
		]);*/

		$this->kernel()->success('Migration created '.$filename);
	}

}