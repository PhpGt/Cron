<?php
namespace Gt\Cron;

use Cron\CronExpression;

class JobRepository {
	public function create(CronExpression $expression, string $command):Job {
		return new Job($expression, $command);
	}
}
