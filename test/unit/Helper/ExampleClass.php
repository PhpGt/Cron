<?php
namespace Gt\Cron\Test\Helper;

class ExampleClass {
	public static $calls = 0;

	public static function doSomething() {
		self::$calls ++;
	}
}