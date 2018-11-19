<?php
namespace Gt\Cron\Test;

use DateTime;
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

	public function testTimeOfNextJob() {
		$expectedTimeOfNextJob = new DateTime("+5 minutes");

		$job = self::createMock(Job::class);
		$job->method("getNextRunDate")
			->willReturn($expectedTimeOfNextJob);

		/** @var Job $job */
		$queue = new Queue();
		$queue->add($job);

		self::assertEquals(
			$expectedTimeOfNextJob,
			$queue->timeOfNextJob()
		);
	}

	public function testSecondsUntilNextJob() {
		$expectedTimeOfNextJob = new DateTime("+5 minutes");

		$job = self::createMock(Job::class);
		$job->method("getNextRunDate")
			->willReturn($expectedTimeOfNextJob);

		/** @var Job $job */
		$queue = new Queue();
		$queue->add($job);

		self::assertEquals(
			60 * 5,
			$queue->secondsUntilNextJob()
		);
	}
}