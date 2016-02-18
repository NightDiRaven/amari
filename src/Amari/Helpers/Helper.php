<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 18.02.2016
 * Time: 13:20
 */

namespace Amari\Helpers;

class Helper {
	protected static $app_path;
	protected static $base_path;
	protected static $config_path;
	protected static $storage_path;
	protected static $config;

	public static function initiate() {
		self::$app_path = realpath('./app') . '/';
		self::$base_path = realpath('.') . '/';
		self::$config_path = realpath('./config/') . '/';
		self::$storage_path = realpath('./resources/') . '/';
		self::loadEnv();

		require_once ('global_helpers.php');
	}

	public static function app_path() {
		return self::$app_path;
	}

	public static function base_path() {
		return self::$base_path;
	}

	public static function storage_path() {
		return self::$storage_path;
	}

	public static function config($value = null) {
		if ($value !== null && (count($config = explode('.', $value)) > 1)) {
			if (isset(self::$config[$config[0]])) {
				$configSection = self::$config[$config[0]];
				return isset($configSection[$config[1]]) ? $configSection[$config[1]] : null;
			}
			elseif (self::loadConfigFile($config[0])) {
				return self::config($value);
			}
		}
		return null;
	}

	protected static function loadConfigFile($name) {
		if (file_exists($configName = self::$config_path . $name . '.php')) {
			return self::$config[$name] = require_once $configName;
		}
		return false;
	}

	protected static function loadEnv() {
		if (file_exists($filename = self::$base_path . '.env'))
			foreach (parse_ini_file($filename) as $name => $value) {
				if (false === getenv($name)) {
					putenv("{$name}={$value}");
				}
				if (!isset($_ENV[$name])) {
					$_ENV[$name] = $value;
				}
			}
	}

}