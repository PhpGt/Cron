<?php
namespace Gt\Cron\Test;

use Cron\CronExpression;
use DateInterval;
use DateTime;
use Gt\Cron\Job;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase {
	public function testIsDueWhenExpressionDue() {
		$expression = $this->mockExpression(0);

		$job = new Job(
			$expression,
			"example"
		);
		self::assertTrue($job->isDue());
	}

	public function testIsDueWhenExpressionDueSuppliedDateTime() {
		$expression = $this->mockExpression(0);

		$job = new Job(
			$expression,
			"example"
		);
		self::assertTrue($job->isDue(new DateTime()));
	}

	public function testIsDueWhenExpressionNotDue() {
		$expression = $this->mockExpression(100);

		$job = new Job(
			$expression,
			"example"
		);
		self::assertFalse($job->isDue());
	}

	public function testGetNextRunDate() {
		$wait = 600;
		$expression = $this->mockExpression($wait);
		$job = new Job(
			$expression,
			"example"
		);

		$expectedRunDate = new DateTime();
		$expectedRunDate->add(
			new DateInterval("PT{$wait}S")
		);

		self::assertDateTimeEquals(
			$expectedRunDate,
			$job->getNextRunDate()
		);
	}

	public function assertDateTimeEquals(
		DateTime $expected,
		DateTime $actual,
		string $message = ""
	) {
		self::assertEquals(
			$expected->format("Y-m-d H:i:s"),
			$actual->format("Y-m-d H:i:s"),
			$message
		);
	}

	protected function mockExpression(int...$wait):CronExpression {
		$runDateCallbackCount = 0;

		$runDate = [];
		$isDue = [];
		foreach($wait as $w) {
			$isDue []= $w < 60;

			$d = new DateTime();
			$d->add(new DateInterval("PT{$w}S"));
			$runDate []= $d;
		}

		$expression = self::createMock(CronExpression::class);
		$expression->method("isDue")
			->willReturnOnConsecutiveCalls(...$isDue);
		$expression->method("getNextRunDate")
			->willReturnCallback(function(DateTime $now)
			use(&$runDateCallbackCount, $runDate) {
				$value = $runDate[$runDateCallbackCount];
				$runDateCallbackCount++;
				return $value;
			});

		/** @var CronExpression $expression */
		return $expression;
	}
}