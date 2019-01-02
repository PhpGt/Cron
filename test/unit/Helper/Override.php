<?php
namespace Gt\Cron\Test\Helper;

class Override {
	protected static $callbackMap = [];

	public static function setCallback(
		string $functionName,
		callable $callback
	):void {
		self::$callbackMap[$functionName] = $callback;
		require_once(implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"OverrideFunction",
			"$functionName.php",
		]));
	}

	public static function call(
		string $functionName,
		array $arguments = []
	):void {
		if(!isset(self::$callbackMap[$functionName])) {
			return;
		}

		call_user_func_array(
			self::$callbackMap[$functionName],
			$arguments
		);
	}
}