<?php

error_reporting(-1);
ini_set("display_errors", 1);

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

Amari\App::instance()->initiate(function(){


	var_dump(db());
	var_dump(storage_path('231'));
	var_dump(base_path());
});

