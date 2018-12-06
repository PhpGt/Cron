<?php
namespace Gt\Cron;

use DateTime;

class QueueRepository {
	protected $className;

	public function __construct(string $className = Queue::class) {
		$this->className = $className;
	}

	public function createAtTime(DateTime &$now):Queue {
		return new $this->className($now);
	}
}