<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 08.07.2016
 * Time: 14:46
 */

namespace Amari\Translatable\Facades;


use Amari\Translatable\Services\Locale;
use Illuminate\Support\Facades\Facade;

class SiteLocale extends Facade {

	public static function getFacadeRoot() {
		return Locale::instance();
	}

	protected static function getFacadeAccessor() {
		return 'locale';
	}
}