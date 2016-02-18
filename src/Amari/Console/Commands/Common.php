<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 16.02.2016
 * Time: 18:53
 */

namespace Amari\Console\Commands;

use Amari\Console\Kernel;

abstract class Common implements ICommand{

	public function exists($subCommand){
		return array_key_exists($subCommand, $this->lists());
	}

	public function doCommand($subCommand, $args = []){
		$methodName = 'do'.ucfirst($subCommand);
		if(method_exists($this, $methodName))
			$this->$methodName($args);
	}

	protected function kernel(){
		return Kernel::instance();
	}

}