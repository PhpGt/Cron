<?php
namespace Gt\Cron\Test;

use DateTime;
use Gt\Cron\Job;
use Gt\Cron\QueueRepository;
use PHPUnit\Framework\TestCase;

class QueueRepositoryTest extends TestCase {
	public function testCreateAtTime() {
		$now = new DateTime();
		$then = [];
		$then []= new DateTime("+4 minutes");
		$then []= new DateTime("+6 minutes");
		$thenToggle = false;
		$later = new DateTime("+5 minutes");

		$job = self::createMock(Job::class);
		$job->method("getNextRunDate")
		->willReturnCallback(function()
		use($then, &$thenToggle) {
			$value = $then[(int)$thenToggle];
			$thenToggle = !$thenToggle;
			return $value;
		});

		$repository = new QueueRepository();
		$queueNow = $repository->createAtTime($now);
		$queueLater = $repository->createAtTime($later);

		$queueNow->add($job);
	}
}