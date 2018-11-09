<?php
namespace Gt\Cron\Test;

use Cron\CronExpression;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase {
	public function testRunnerThrowsExceptionWithInvalidCron() {
		$cronContents = <<<CRON
* * * * * * * * * * ThisShouldNotWork::example
CRON;

		self::expectException(ParseException::class);
		$runner = new Runner($cronContents);
	}
}