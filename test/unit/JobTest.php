<?php
namespace Gt\Cron\Test;

use Cron\CronExpression;
use DateInterval;
use DateTime;
use Gt\Cron\CronException;
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

	public function testGetNextRunDateWithSuppliedDate() {
		$wait = 900;
		$expression = $this->mockExpression($wait);
		$job = new Job(
			$expression,
			"example"
		);

		$expectedRunDate = new DateTime();
		$expectedRunDate->add(
			new DateInterval("PT{$wait}S")
		);
		$now = new DateTime();
		$now->add(new DateInterval("PT150S"));

		self::assertDateTimeEquals(
			$expectedRunDate,
			$job->getNextRunDate()
		);
	}

	public function testGetCommand() {
		$job1 = new Job(
			$this->mockExpression(),
			$id1 = uniqid()
		);
		$job2 = new Job(
			$this->mockExpression(),
			$id2 = uniqid()
		);

		self::assertEquals($id2, $job2->getCommand());
		self::assertEquals($id1, $job1->getCommand());
	}

	public function testRunHasRun() {
		$job = new Job(
			$this->mockExpression(),
			"example"
		);

		self::assertFalse($job->hasRun());
		try {
			$job->run();
		}
		catch(CronException $exception) {}

		self::assertTrue($job->hasRun());
	}

	public function testResetRunFlag() {
		$job = new Job(
			$this->mockExpression(),
			"example"
		);

		try {
			$job->run();
		}
		catch(CronException $exception) {}
		$job->resetRunFlag();
		self::assertFalse($job->hasRun());
	}

	public static function assertDateTimeEquals(
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
			->willReturnCallback(function()
			use(&$runDateCallbackCount, $runDate) {
				$value = $runDate[$runDateCallbackCount];
				$runDateCallbackCount++;
				return $value;
			});

		/** @var CronExpression $expression */
		return $expression;
	}
}