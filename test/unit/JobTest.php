<?php
namespace Gt\Cron\Test;

use Cron\CronExpression;
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

	protected function mockExpression(int...$wait):CronExpression {
		$isDue = [];
		foreach($wait as $w) {
			$isDue []= $w < 60;
		}

		$expression = self::createMock(CronExpression::class);
		$expression->method("isDue")
			->willReturnOnConsecutiveCalls(...$isDue);

		/** @var CronExpression $expression */
		return $expression;
	}
}