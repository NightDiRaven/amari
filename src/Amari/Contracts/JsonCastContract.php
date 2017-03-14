<?php

namespace Amari\Contracts;

use Illuminate\Support\Collection;

interface JsonCastContract {

	/**
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * @return Collection
	 */
	public function toCollection(): Collection;

	/**
	 * Format json field with keys to value, example ['key' => 'image', 'key2 => callback, ...]
	 *
	 * @param array $fieldFormat
	 *
	 * @return mixed
	 */
	public function cast(array $fieldFormat = []);

	/**
	 * Cast return result array
	 *
	 * @param array $fieldFormat
	 *
	 * @return array
	 */
	public function castArray(array $fieldFormat = []) : array;
}