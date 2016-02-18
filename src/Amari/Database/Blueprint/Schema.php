<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 15:41
 */

namespace Amari\Database\Blueprint;

class Schema {

	protected $table;
	protected $blueprint;
	protected $sql;

	public function __construct($table) {
		$this->table = $table;
	}

	public function createTable($blueprintCallback){
		$this->blueprint = new Blueprint();
		$blueprintCallback($this->blueprint);

		$this->build();
	}

	public static function create($table, $blueprintCallback){
		$schema = new Schema($table);
		$schema->createTable($blueprintCallback);
	}

	public static function drop($table){
		$schema = new Schema($table);
		$schema->dropTable();
	}

	protected function index($colsString, $unique = false){
		return "CREATE".($unique?" UNIQUE":"")." INDEX ".$this->table."_unq_".str_replace(',', '_', $colsString)." ON ".$this->table."(".$colsString.");";
	}

	public function build(){
		$sql = "CREATE table IF NOT EXISTS $this->table(";
		$postSQL = [];

		foreach($this->blueprint->getFields() as $field){
			$raw = $field->getRaw();

			$sql .= $raw['name'].' '.
					$raw['type'].
					(isset($raw['opt'])?'('.$raw['opt'].')':'').
					((isset($raw['references']) && $raw['references']->full())?
						(' references '.$raw['references']->on.'('.$raw['references']->field.')'.($raw['references']->on_delete?' on delete '.$raw['references']->on_delete:'')):
						(isset($raw['primary']) && $raw['primary']?' PRIMARY KEY':'').
						(isset($raw['nullable']) && $raw['nullable']?'':' NOT NULL').', ');

			if(isset($raw['index']) && $raw['index']) $postSQL[] = $this->index($raw['name']);
			elseif(isset($raw['unique']) && $raw['unique']) $postSQL[] =  $this->index($raw['name'], true);
		}

		$this->sql = rtrim(trim($sql), ",").');';

		db()->pdo()->exec($this->sql);

		foreach($this->blueprint->getIdxs() as $array){
			$postSQL[] = $this->index(implode(",", $array));
		}
		foreach($this->blueprint->getUniq() as $array){
			$postSQL[] = $this->index(implode(",", $array), true);
		}
		foreach($postSQL as $sql){
			var_dump($sql);
			var_dump(db()->pdo()->exec($sql));
		}
	}

	public function dropTable(){

		$this->sql = "DROP table $this->table;";
		db()->pdo()->exec($this->sql);
	}



}