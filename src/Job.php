<?php
namespace Gt\Cron;

use Cron\CronExpression;
use DateTime;

class Job {
	protected $expression;
	protected $command;
	protected $hasRun;

	public function __construct(CronExpression $factory, string $command) {
		$this->expression = $factory;
		$this->command = $command;
		$this->hasRun = false;
	}

	public function isDue(DateTime $now = null):bool{
		if(is_null($now)) {
			$now = new DateTime();
		}

		return $this->expression->isDue($now);
	}

	public function getNextRunDate(DateTime $now = null):DateTime {
		if(is_null($now)) {
			$now = new DateTime();
		}
		return $this->expression->getNextRunDate($now);
	}

	public function getCommand():string {
		return $this->command;
	}

	public function run():void {
		if($this->isFunction()) {
			$this->executeFunction();
		}
		else {
// Assume the command is a shell command.
			$this->executeScript();
		}

		$this->hasRun = true;
	}

	public function hasRun():bool {
		return $this->hasRun;
	}

	public function resetRunFlag():void {
		$this->hasRun = false;
	}

	public function isFunction():bool {
		$command = $this->command;
		$bracketPos = strpos(
			$command,
			"("
		);
		if($bracketPos !== false) {
			$command = substr($command, 0, $bracketPos);
			$command = trim($command);
		}

		return is_callable($command);
	}

	protected function executeFunction():void {
		$command = $this->command;
		$args = [];
		$bracketPos = strpos($command, "(");
		if($bracketPos !== false) {
			$argsString = substr(
				$command,
				$bracketPos
			);
			$argsString = trim($argsString, " ();");
			$args = str_getcsv($argsString);

			$command = substr(
				$command,
				0,
				$bracketPos
			);
			$command = trim($command);
		}

		call_user_func_array($command, $args);
	}

	protected function executeScript():void {

	}
}