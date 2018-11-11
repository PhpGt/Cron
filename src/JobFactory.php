<?php
namespace Gt\Cron;

use Cron\CronExpression;

class JobFactory {
	public static function create(CronExpression $expression, $command):Job {
		return new Job($expression, $command);
	}
}