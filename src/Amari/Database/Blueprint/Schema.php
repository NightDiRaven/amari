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
	protected $relationField;
	protected $sql;

	public function __construct($table) {
		$this->table = $table;
	}

	public static function create($table, $blueprintCallback) {
		$schema = new Schema($table);
		return $schema->createTable($blueprintCallback);
	}

	public static function drop($table) {
		$schema = new Schema($table);
		return $schema->dropTable();
	}

	public static function relation($table, $referenceBlueprint) {
		$schema = new Schema($table);
		return $schema->relationTable($blueprintCallback);
	}

	protected function relationTable($blueprintCallback) {
		$this->blueprint = new Blueprint();
		$blueprintCallback($this->blueprint);
		return $this->build();
	}

	protected function createTable($blueprintCallback) {
		$this->blueprint = new Blueprint();
		$blueprintCallback($this->blueprint);

		return $this->build();
	}

	protected function index($colsString, $unique = false) {
		return "CREATE" . ($unique ? " UNIQUE" : "") . " INDEX " . $this->table . "_unq_" . str_replace(',', '_', $colsString) . " ON " . $this->table . "(" . $colsString . ");";
	}

	protected function build() {
		$sql = "CREATE table IF NOT EXISTS $this->table(";
		$postSQL = [];

		foreach ($this->blueprint->getFields() as $field) {
			$raw = $field->getRaw();

			if ($raw['type'] == 'custom')
				$sql .= $raw['name'];
			else
				$sql .= $raw['name'] . ' ' .
					$raw['type'] .
					(isset($raw['opt']) ? '(' . $raw['opt'] . ')' : '') .
					((isset($raw['references']) && $raw['references']->full()) ?
						(' references ' . $raw['references']->on . '(' . $raw['references']->field . ')' . ($raw['references']->on_delete ? ' on delete ' . $raw['references']->on_delete : '')) :
						(isset($raw['primary']) && $raw['primary'] ? ' PRIMARY KEY' : '') .
						(isset($raw['default']) ?
							(' DEFAULT ' . $raw['default']) :
							(isset($raw['nullable']) && $raw['nullable'] ? '' : ' NOT NULL'))) . ', ';

			if (isset($raw['index']) && $raw['index']) $postSQL[] = $this->index($raw['name']);
			elseif (isset($raw['unique']) && $raw['unique']) $postSQL[] = $this->index($raw['name'], true);
		}

		if (db()->raw(rtrim(trim($sql), ",") . ');')) {

			foreach ($this->blueprint->getIdxs() as $array) {
				db()->raw($this->index(implode(",", $array)));
			}
			foreach ($this->blueprint->getUniq() as $array) {
				db()->raw($this->index(implode(",", $array), true));
			}
			foreach ($postSQL as $sql) {
				db()->raw($sql);
			}

			return true;
		}
		return false;
	}

	protected function dropTable() {

		return db()->raw("DROP table $this->table CASCADE;");
	}


}