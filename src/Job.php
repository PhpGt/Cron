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
		$this->hasRun = true;
	}

	public function hasRun():bool {
		return $this->hasRun;
	}

	public function resetRunFlag():void {
		$this->hasRun = false;
	}
}