<?php

namespace Amari\Translatable\Services;

use Amari\Translatable\Models\Language;
use \Schema;

class Locale {

	protected static $instance;
	protected static $prefix;
	protected $app;

	protected $id;
	protected $locale;
	protected $main;
	protected $langs = [];

	public function __construct() {
		$this->app = app();
		if (1 or Schema::hasTable('langs')) {
			if (count($this->full = Language::all('id', 'code', 'main', 'title')) == 0) {
				$this->full = collect([Language::addMainLocale()]);
			}
		} else {
			$this->full = collect([new Language(['id' => 1, 'code' => $this->app->getLocale()])]);
		}
		$this->langs = $this->full->pluck('id', 'code')->toArray();
		$this->main = $this->full->filter(function ($i) {
				return $i->main;
			})->first() ?? $this->full->first();

		$this->set($this->main->code, true);
	}

	public function set($code, $force = false) {
		if ($force or $this->exists($code)) {
			$this->app->setLocale($this->locale = $code);
			$this->id = $this->id($this->locale);

			return $code;
		}
	}

	public function getLocale() : string {
		return $this->locale;
	}

	public function exists($code) : bool {
		return array_key_exists($code, $this->langs);
	}

	public function isMain($code) {
		return $this->main->code == $code or $this->main->id == $code;
	}

	public function id($code = null) {
		return $this->exists($code) ? $this->langs[$code] : $this->id;
	}

	public function langs() {
		return $this->langs;
	}

	public function otherLangs() {
		$langs = $this->langs;
		if (isset($langs[$this->main->code])) unset($langs[$this->locale]);

		return $langs;
	}

	public function all() {
		return Language::all();
	}

	public function main() {
		return $this->main;
	}

	public function prefix() {
		if (!self::$prefix) if (array_key_exists($lang = request()->segment(1), $this->otherLangs())) {
			$this->set($lang);
			self::$prefix = $lang;
		} elseif ($lang == $this->main->code) {
			\Route::get($lang, function () {
				return redirect('/');
			});
			self::$prefix = '';
		}

		return self::$prefix;
	}

	public function switchRedirect($code) {
		$this->set($code);
		$url = array_values(array_filter(explode('/', url()->previous()), function ($v) {
			return $v != '' and $v != 'http:' and $v != $_SERVER['SERVER_NAME'] and $v != $_SERVER['HTTP_HOST'];
		}));
		if (isset($url[0]) and $this->exists($url[0])) {
			if (!$this->isMain($code))
				$url[0] = $code;
			else unset($url[0]);
		} else if (!$this->isMain($code))
			array_unshift($url, $code);

		return redirect(implode('/', $url));
	}

	public static function instance() : Locale {
		if (!(self::$instance instanceof self)) self::$instance = new self();

		return self::$instance;
	}
}