<?php

//GLOBAL NAMESPACE FOR THE NAME OF KURONEKO

function camel_case($string) {
	return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
}

function env($param1, $param2) {
	return (false !== ($value = getenv($param1))) ? $value : $param2;
}

function app_path($value = '') {
	return \Amari\Helpers\Helper::app_path() . $value;
}

function base_path($value = '') {
	return \Amari\Helpers\Helper::base_path() . $value;
}

/**
 * @param string $value
 * @return string
 */
function storage_path($value = '') {
	return \Amari\Helpers\Helper::storage_path() . $value;
}

/**
 * Return config value (dotted one level depth, example app.name)
 *
 * @param $value
 * @return mixed
 */
function config($value = null) {
	if($value !== null)
		return \Amari\Helpers\Helper::config($value);
}

/**
 * Return instance of application class
 *
 * @return \Amari\App
 */
function app() {
	return \Amari\App::instance();
}

/**
 * Return instance of database class
 *
 * @return \Amari\Database\DB
 */
function db() {
	return \Amari\Database\DB::instance();
}
