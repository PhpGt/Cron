<?php
namespace Gt\Cron\Test;

use Gt\Cron\Job;
use Gt\Cron\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase {
	public function testGetNextJobEmpty() {
		$queue = new Queue();
		self::assertNull($queue->getNextJob());
	}

	public function testNextJobNotEmpty() {
		/** @var Job $job */
		$job = self::createMock(Job::class);
		$queue = new Queue();
		$queue->add($job);
		self::assertNotNull($queue->getNextJob());
	}
}