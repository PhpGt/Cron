<?php
namespace Gt\Cron\Test;

use Gt\Cron\ParseException;
use Gt\Cron\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase {
	public function testParseException() {
		$cronContents = <<<CRON
* * * ABC * ThisShouldNotWork::example
CRON;

		self::expectException(ParseException::class);
		new Runner($cronContents);
	}

	public function testParseExceptionIdentifiesLine() {
		$cronContents = <<<CRON
0 22 * * 1-5 CronExample::TenOclockWeekday
* * * * * CronExample::everyMinute
15 00 * CronBadExample::notEnoughParts
30 12 1 * * CronExample::halfTwelveOnFirstDayOfMonth
CRON;

		self::expectException(ParseException::class);
		self::expectExceptionMessage("Error parsing cron: 15 00 * CronBadExample::notEnoughParts");
		new Runner($cronContents);
	}
}