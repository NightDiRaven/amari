<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 19.02.2016
 * Time: 14:47
 */

namespace Amari\Database\Blueprint\Table\Query;


class SubQuery {
	protected $where = [];

	protected function whereRule($field, $eqOrValue, $value = null, $hard = true){
		return [
			'field' => $field,
			'sign' => $eqOrValue,
			'value' => $value,
			'and' => $hard
		];
	}

	public function where($field, $eqOrValue = null, $value = null, $hard = true){
		if(is_callable($field)){
			$subQuery = new SubQuery();

			$this->where = $this->whereRule($field, $eqOrValue, $value, $hard);
			$field($subQuery);
		} elseif($value === null) {
			$value = $eqOrValue;
			$eqOrValue = '=';
		} else {
			$this->where = $this->whereRule($field, $eqOrValue, $value, $hard);
		}
		return $this;
	}

	public function orWhere($field, $eqOrValue, $value = null){
		return $this->where($field, $eqOrValue, $value, false);
	}

	public function whereIn($field, Array $values, $hard = true){
		$this->where = $this->whereRule($field, $eqOrValue, $value, $hard);
		return $this;
	}

	public function orWhereIn($field, Array $values){
		return $this->whereIn($field, $values, false);
	}
}