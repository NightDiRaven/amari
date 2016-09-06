<?php

namespace Amari\Translatable\Traits;

use Amari\Translatable\Contracts\JsonableContract;
use Amari\Translatable\Models\Language;
use Amari\Translatable\SchemaLanguage;
use Amari\Translatable\Services\Locale;
use Illuminate\Database\Eloquent\Model;

trait Translatable {

	public $dirty = [];
	public $instances = [];

	public function langs() {
		$table = $this->getTable();
		return $this->belongsToMany(Language::class, SchemaLanguage::formatName($table), SchemaLanguage::formatForeign($table), 'lang_id')
					->withPivot($this->translatable);
	}

	public function scopeTranslated($q, $lang = null) {
		return ((($code = Locale::instance()->id($lang)) and !Locale::instance()>isMain($code))) ? $q->with('langs')->whereHas('langs', function ($q) use ($code) {
			$q->where('code', '=', $code);
		}) : $q;
	}

	public function transMorph($lang = null) {
		foreach ($this->trans($lang) as $name=>$value)
			$this->$name = $value;
		return $this;
	}

	public function transWith(array $relations = [], $lang = null) {
		$this->transMorph($lang);

		foreach ($relations as $name) if($this->$name instanceof Model) $this->$name->transMorph($lang);
			else foreach ($this->$name as $item) $item->transMorph($lang);
		return $this;
	}

	public static function transMorphAll($items, $lang = null){
		foreach ($items as $item){
			$item->transMorph($lang);
		}
		return $items;
	}

	public function cloneMorph($lang = null) {
		$clone = clone $this;
		$clone->jsonArray = [];
		$clone->transMorph($lang);
		return $clone;
	}

	public function trans($lang = null) : array {

		if($id = Locale::instance()->id($lang) and !Locale::instance()->isMain($id)) {
			$lang = $this->langs->filter(function ($i) use ($id) {
				return $i->id == $id;
			})->first();
			if($lang) return $lang->pivot->toArray();
		}
		return [];
	}

	public function saveLangs(){
		$prepare = [];

		if($this->dirty) {
			if ($this instanceof JsonableContract) $prepare = $this->saveJson();
			else foreach ($this->dirty as $lang => $attrs) if ($id = Locale::instance()->id($lang)) $prepare[$id] = $attrs;
		}
		$this->langs()->sync($prepare);
	}

	public function saveJson(){
		$prepare = [];
		if($this->dirty) foreach ($this->dirty as $lang => $attrs) if($id = Locale::instance()->id($lang)){
			$clone = clone $this;
			foreach ($attrs as $name=>$value)
				$clone->$name = $value;
			$one = [];
			foreach($clone->getTranslatable() as $translatable)
				$one[$translatable] = $clone->$translatable;
			$prepare[$id] = $one;
		}
		return $prepare;
	}

	public function setAttribute($key, $value) {
		return $this->setDirtyAttribute($key, $value) ?? parent::setAttribute($key, $value);
	}

	public function setDirtyAttribute($key, $value) {

		if( ends_with($key, '__')) {
			$parts = explode('__', $key);
			if(!isset($this->dirty[$parts[1]])) $this->dirty[$parts[1]] = [];
			$this->dirty[$parts[1]][$parts[0]] = $value;

			return true;
		} else return null;
	}

	public function getAttribute($key) {
		return $this->getTransAttribute($key) ?? parent::getAttribute($key);
	}

	public function getTransAttribute($key){
		if( ends_with($key, '__')) {
			$parts = explode('__', $key);
			$v = $this->cloneMorph($parts[1]);
			return $v->getAttribute($parts[0]);
		} else return null;
	}

	public function getTranslatable() : array {
		return $this->translatable;
	}

	public function getJson() : array {
		return isset(static::$json) ? static::$json : [];
	}

	/**
	 * Get all translation of model and fill for save
	 *
	 * @param array $data
	 * @param array $exclude
	 * @return $this
	 */
	public function expand(array $data = [], $exclude = ['content']) {
		foreach (\Amari\Translatable\Services\Locale::instance()->otherLangs() as $lang=>$id){
			$tranModel = $this->cloneMorph($lang);
			foreach ($this->getJson() as $field => $options) foreach ($options as $param) {
				$paramName =$param.'__'.$lang.'__';
				$this->$paramName = $tranModel->$param;
			}
			foreach ($this->getTranslatable() as $param) if(!in_array($param, $exclude)) {
				$paramName = $param.'__'.$lang.'__';
				$this->$paramName = $tranModel->$param;
			}

		}
		foreach ($data as $item => $value){
			$this->$item = $value;
		}
		return $this;
	}

}