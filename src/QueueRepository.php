<?php
namespace Gt\Cron;

use DateTime;

class QueueRepository {
	public function createAtTime(DateTime &$now):Queue {
		return new Queue($now);
	}
}