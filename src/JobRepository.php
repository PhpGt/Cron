<?php
namespace Gt\Cron;

use Cron\CronExpression;

class JobRepository {
	public function create(CronExpression $expression, $command):Job {
		return new Job($expression, $command);
	}
}