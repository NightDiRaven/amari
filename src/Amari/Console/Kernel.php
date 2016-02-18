<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 16.02.2016
 * Time: 11:46
 */

namespace Amari\Console;

class Kernel {

	protected static $_instance;
	protected $args;
	protected $line = false;
	protected $commands = [];

	protected function __construct() {
		global $argv;
		$this->args = $argv;

		if(isset($argv[1])) $this->line = $argv[1];
	}

	protected function __clone() { }

	public static function instance($params = []){

		if(!(self::$_instance instanceof self))
			self::$_instance = new Kernel($params);


		return self::$_instance;
	}


	public function register($class){
		$classInstance = new $class;
		if(in_array(Commands\ICommand::class, class_implements($class)))
			$this->commands[$classInstance->title()] = $classInstance;
		else
			$this->error('Class '.$class.' not implement Amari\Console\Commands\ICommand interface');
		return $this;
	}

	public function color($text, $color = 0){
		return "\033[".$color."m".$text;
	}

	public function format($text, $rows = 0, $tabs = 0){
		return str_repeat("\t", $tabs).$text.str_repeat("\n",$rows);
	}

	public function tabString($text){
		$this->send($this->format($text, 1, 1));
		return $this;
	}

	public function string($text){
		$this->send($this->format($text, 1));
		return $this;
	}

	public function error($text){
		$this->send($this->format($this->color($text, 31), 1, 1));
		return $this;
	}

	public function success($text){
		$this->send($this->format($this->color($text, 32), 1, 1));
		return $this;
	}

	public function say($text, $color = 35){
		$this->send($this->format($this->color($text, $color), 1));
		return $this;
	}

	public function br($count = 1){
		if($count > 0) $this->send($this->format('', $count));
		return $this;
	}

	public function tab($count = 1){
		if($count > 0) $this->send($this->format('', 0, $count));
		return $this;
	}

	public function clear(){
		echo $this->color('', 0);
		return $this;
	}

	public function get(){
		return trim(fgets(fopen("php://stdin", "r")));
	}

	public function send($text){
		echo ' '.$text;
		return $this->clear();
	}

	public function handle(){

		if(!$this->line) {
			$this->say('Amari says, "Hi there, it\'s dangerous to go alone, take this list of commands!"')->br();

			foreach($this->commands as $title=>$class){
				$this->string($this->color($title, '33'));
				foreach($class->lists() as $name=>$desc)
					$this->send($this->color(' '.$title.':'.$name, '32'))
						->tab(strlen($name)>7?1:2)
						->string($this->color($desc, 0));
			}

			$this->br();
		} else {
			$route = explode(":", $this->line);
			if (array_key_exists($route[0], $this->commands)) {
				$command = $this->commands[$route[0]];
				if(!isset($route[1])) $route[1] = 'index';
				if($command->exists($route[1]))
					$command->doCommand($route[1], $this->args);
				else
					$command->index($route[1], $this->args);
			} else {
				$this->error('Command '.$this->line.' not exists');
			}
		}
	}
}