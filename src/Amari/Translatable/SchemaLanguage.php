<?php
namespace Amari\Translatable;

use Illuminate\Database\Schema\Blueprint;
use \Schema;

class SchemaLanguage {

	/**
	 * Function formatting translation table name from original table name
	 *
	 * @param string $name
	 * @return string
	 */
	public static function formatName(string $name) : string {
		return config('langs.db.prefix') . str_singular($name) . config('langs.db.postfix');
	}

	/**
	 * Default format for foreign keys
	 *
	 * @param string $name
	 * @return string
	 */
	public static function formatForeign(string $name) : string {
		return str_singular($name) . '_id';
	}

	/**
	 * Create table and translation table
	 *
	 * @param string $name
	 * @param callable $not_translatable
	 * @param callable $translatable
	 * @param null $only_translatable
	 * @param null $foreign
	 * @param string $refs
	 */
	public static function create(string $name, callable $not_translatable, callable $translatable, $only_translatable = null, $foreign = null, $refs = 'id') {
		self::apply('create', $name, $not_translatable, $translatable);
		self::apply('create', self::formatName($name), self::getCallback($name, $only_translatable, $foreign, $refs), $translatable);
	}
	
	/**
	 * Modify table and translation table
	 *
	 * @param string $name
	 * @param callable $not_translatable
	 * @param callable $translatable
	 * @param callable $only_translatable
	 * @param null $foreign
	 * @param string $refs
	 */
	public static function table(string $name, callable $not_translatable, callable $translatable, callable $only_translatable, $foreign = null, $refs = 'id') {
		self::apply('table', $name, $not_translatable, $translatable);
		self::apply('table', self::formatName($name), $only_translatable, $translatable);
	}

	/**
	 * Drop table and translation table
	 *
	 * @param $name
	 */
	public static function drop($name) {
		Schema::drop(self::formatName($name));
		Schema::drop($name);
	}

	/**
	 * Create Langs Table
	 *
	 * @return Blueprint
	 */
	public static function createLangsTable() {
		$tname = config('langs.db.table_name');
		return Schema::hasTable($tname) ? : Schema::create($tname, function (Blueprint $table) {
			$table->increments('id');
			$table->string('title', 120);
			$table->boolean('main')->default(false);
			$table->string('code', 20)->unique();
		});
	}

	/**
	 * Drops Langs Table
	 *
	 * @return Blueprint
	 */
	public static function dropLangsTable() {
		$tname = config('langs.db.table_name');
		return !Schema::hasTable($tname) ? : Schema::drop($tname);
	}

	/**
	 * Call default schema builder with two serial callbacks
	 *
	 * @param $method
	 * @param $name
	 * @param callable $nt
	 * @param callable $tr
	 */
	protected static function apply($method, $name, callable $nt, callable $tr) {
		forward_static_call_array([Schema::class, $method], [
			$name,
			function (Blueprint $b) use ($nt, $tr) {
				$nt($b);
				$tr($b);
			},
		]);
	}

	/**
	 * Return callback for instantiating table with langs strings
	 *
	 * @param string $name
	 * @param $only_translatable
	 * @param $foreign
	 * @param string $refs
	 * @return callable
	 */
	protected static function getCallback(string $name, $only_translatable, $foreign, string $refs) : callable {
		$tname = $foreign ?? self::formatForeign($name);
		$langs = config('langs.db.table_name');
		$tlangs = self::formatForeign($langs);
		return function (Blueprint $table) use ($only_translatable, $name, $tname, $langs, $tlangs, $refs) {
			$table->integer($tname)->unsigned();
			$table->foreign($tname)->references($refs)->on($name)->onDelete('cascade');
			$table->integer($tlangs)->unsigned();
			$table->foreign($tlangs)->references('id')->on($langs)->onDelete('cascade');
			$table->unique([$tname, $tlangs]);
			if (is_callable($only_translatable)) $only_translatable($table);
		};
	}
}