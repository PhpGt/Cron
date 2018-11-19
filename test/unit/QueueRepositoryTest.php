<?php
namespace Gt\Cron\Test;

use DateTime;
use Gt\Cron\Job;
use Gt\Cron\QueueRepository;
use PHPUnit\Framework\TestCase;

class QueueRepositoryTest extends TestCase {
	public function testCreateAtTime() {
		$now = new DateTime("+17 minutes");
		$repository = new QueueRepository();
		$queue = $repository->createAtTime($now);
		self::assertEquals($now, $queue->now());
	}
}