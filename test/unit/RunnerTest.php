<?php
namespace Gt\Cron\Test;

use DateTime;
use Gt\Cron\Job;
use Gt\Cron\JobFactory;
use Gt\Cron\ParseException;
use Gt\Cron\Runner;
use Gt\Cron\Test\Helper\Override;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase {
	public function testParseException() {
		$cronContents = <<<CRON
* * * ABC * ThisShouldNotWork::example
CRON;

		self::expectException(ParseException::class);
		new Runner(
			$this->mockJobFactory(),
			$cronContents
		);
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
		new Runner($this->mockJobFactory(),
			$cronContents
		);
	}

	public function testRun() {
		$now = new DateTime("2020-01-01 12:10:00");
		$cronContents = <<<CRON
10 * * * * ExampleClass::runAtTenMinutesPast
15 * * * * ExampleClass::runAtFifteenMinutesPast
CRON;

		$runner = new Runner(
			$this->mockJobFactory(),
			$cronContents,
			$now
		);
		self::assertEquals(
			1,
			$runner->run(true)
		);
	}

	public function testMultipleJobsRun() {
		$now = new DateTime("2020-01-01 12:25:00");
		$cronContents = <<<CRON
10 * * * * ExampleClass::runAtTenMinutesPast
25 * * * * ExampleClass::runAtTwentyFiveMinutesPast
*/5 * * * * ExampleClass::runEveryFiveMinutes
*/10 * * * * ExampleClass::runEveryTenMinutes
CRON;

		$runner = new Runner(
			$this->mockJobFactory(),
			$cronContents,
			$now
		);
		self::assertEquals(
			2,
			$runner->run(true)
		);
	}

	public function testSleep() {
// Set now to midday with a cron job at 10 past.
		$now = new DateTime("2020-01-01 12:00:00");
		$cronContents = <<<CRON
10 * * * * ExampleClass::runAtTenMinutesPast
CRON;

		$runner = new Runner(
			$this->mockJobFactory(),
			$cronContents,
			$now
		);

		$secondsUntilNextJob = 0;

		Override::setCallback(
			"sleep",
		function($seconds) use(&$secondsUntilNextJob, $runner) {
			$secondsUntilNextJob = $seconds;
			$runner->stop = true;
		});

		$runner->run();
		self::assertEquals(600, $secondsUntilNextJob);
	}

	protected function mockJobFactory(string...$jobCommands):JobFactory {
		$job = self::createMock(Job::class);

		$factory = self::createMock(JobFactory::class);
		$factory->method("create")
			->willReturn($job);

		/** @var JobFactory $factory */
		return $factory;
	}
}