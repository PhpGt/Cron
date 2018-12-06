<?php
namespace Gt\Cron\Test;

use DateInterval;
use DateTime;
use Gt\Cron\Job;
use Gt\Cron\JobRepository;
use Gt\Cron\ParseException;
use Gt\Cron\Queue;
use Gt\Cron\QueueRepository;
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
			$this->mockJobRepository(),
			$this->mockQueueRepository(),
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
		new Runner(
			$this->mockJobRepository(),
			$this->mockQueueRepository(),
			$cronContents
		);
	}

	public function testRun() {
		$now = new DateTime("2020-01-01 12:10:00");
		$cronContents = <<<CRON
10 * * * * ExampleClass::runAtTenMinutesPast
15 * * * * ExampleClass::runAtFifteenMinutesPast
CRON;

		$expectedWait = [
			0,
			5 * 60
		];

		$runner = new Runner(
			$this->mockJobRepository(
				...$expectedWait
			),
			$this->mockQueueRepository(
				...$expectedWait
			),
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

		$expectedWait = [
			45 * 60,
			0,
			0,
			5 * 60
		];

		$runner = new Runner(
			$this->mockJobRepository(
				...$expectedWait
			),
			$this->mockQueueRepository(
				...$expectedWait
			),
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
		$expectedWait = [
			10 * 60,
		];

		$runner = new Runner(
			$this->mockJobRepository(
				...$expectedWait
			),
			$this->mockQueueRepository(
				...$expectedWait
			),
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

	public function testSleepGetsEarliest() {
// Set now to midday with a cron job at 10 past.
		$now = new DateTime("2020-01-01 12:00:00");
		$cronContents = <<<CRON
10 * * * * ExampleClass::runAtTenMinutesPast
*/5 * * * * ExampleClass::runEveryFiveMinutes
CRON;

		$expectedWait = [
			10 * 60,
			5 * 60,
		];

		$runner = new Runner(
			$this->mockJobRepository(...$expectedWait),
			$this->mockQueueRepository(...$expectedWait),
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
		self::assertEquals(300, $secondsUntilNextJob);
	}

	public function testRunNumber() {
		$cronContents = <<<CRON
* * * * * ExampleClass::one
* * * * * ExampleClass::two
* * * * * ExampleClass::three
CRON;
		$expectedWait = [
			0,
			0,
			0,
		];

		$runner = new Runner(
			$this->mockJobRepository(...$expectedWait),
				$this->mockQueueRepository(...$expectedWait),
				$cronContents
		);

		self::assertEquals(
			3,
			$runner->run(true)
		);
	}

	public function testRunNumberWithBlankLines() {
		$cronContents = <<<CRON
* * * * * ExampleClass::one

* * * * * ExampleClass::two
* * * * * ExampleClass::three


CRON;
		$expectedWait = [
			0,
			0,
			0,
		];

		$runner = new Runner(
			$this->mockJobRepository(...$expectedWait),
			$this->mockQueueRepository(...$expectedWait),
			$cronContents
		);

		self::assertEquals(
			3,
			$runner->run(true)
		);
	}

	public function testRunCallbackIsExecuted() {
		$cronContents = <<<CRON
* * * * * ExampleClass::example
CRON;
		$expectedWait = [
			0,
		];

		$runner = new Runner(
			$this->mockJobRepository(...$expectedWait),
			$this->mockQueueRepository(...$expectedWait),
			$cronContents
		);

		$count = 0;

		$runner->setRunCallback(function() use(&$count) {
			$count++;
		});

		self::assertEquals(0, $count);
		$runner->run(true);
		self::assertEquals(1, $count);
		$runner->run(true);
		self::assertEquals(2, $count);
	}

	public function testComments() {
		$cronContents = <<<CRON
* * * * * ExampleClass::example1
#* * * * * ExampleClass::example2
* * * * * ExampleClass::example3
#* * * * * ExampleClass::example4
# * * * * * ExampleClass::example5
CRON;

		$runner = new Runner(
			$this->mockJobRepository(0),
			$this->mockQueueRepository(0),
			$cronContents
		);
		self::assertEquals(2, $runner->getNumJobs());
	}

	protected function mockJobRepository(int...$wait):JobRepository {
		$isDue = [];
		$runDate = [];
		foreach($wait as $w) {
			$isDue []= $w === 0;
			$d = new DateTime();
			$d->add(new DateInterval("PT{$w}S"));
			$runDate []= $d;
		}

		$job = self::createMock(Job::class);
		$job->method("isDue")
			->willReturnOnConsecutiveCalls(...$isDue);
		$job->method("getNextRunDate")
			->willReturnOnConsecutiveCalls(...$runDate);

		$repository = self::createMock(JobRepository::class);
		$repository->method("create")
			->willReturn($job);



		/** @var JobRepository $repository */
		return $repository;
	}

	protected function mockQueueRepository(int...$wait):QueueRepository {
		$numberDueJobs = 0;
		$secondsUntilNextJob = null;

		foreach($wait as $w) {
			if($w === 0) {
				$numberDueJobs++;
			}

			if(is_null($secondsUntilNextJob)
			|| $w < $secondsUntilNextJob) {
				$secondsUntilNextJob = $w;
			}
		}

		$queue = self::createMock(Queue::class);
		$queue->method("runDueJobs")
			->willReturn($numberDueJobs);
		$queue->method("secondsUntilNextJob")
			->willReturn($secondsUntilNextJob);

		$repository = self::createMock(QueueRepository::class);
		$repository->method("createAtTime")
			->willReturn($queue);

		/** @var QueueRepository $repository */
		return $repository;
	}
}