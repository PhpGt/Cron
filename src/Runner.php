<?php
namespace Gt\Cron;

use Cron\CronExpression;
use DateTime;
use InvalidArgumentException;

class Runner {
	/** @var bool */
	public $stop;
	/** @var Queue */
	protected $queue;
	/** @var callable */
	protected $runCallback;

	public function __construct(
		JobRepository $jobRepository,
		QueueRepository $queueRepository,
		string $contents,
		DateTime &$now = null
	) {
		if(is_null($now)) {
			$now = new DateTime();
		}

		$this->queue = call_user_func_array(
			[$queueRepository, "createAtTime"],
			[&$now]
		);

		foreach(explode("\n", $contents) as $line) {
			$line = trim($line);
			if(strlen($line) === 0) {
				continue;
			}

			preg_match(
				"/(?P<crontab>\S+\s\S+\s\S+\s\S+\s\S+)\s(?P<command>.+)/",
				$line,
				$matches
			);

			$crontab = $matches["crontab"] ?? null;
			$command = $matches["command"] ?? null;

			try {
// TODO: This is a concern for unit tests... calling a static method is impossible to test.
				$job = call_user_func(
					[$jobRepository, "create"],
					CronExpression::factory($crontab),
					$command
				);
				$this->queue->add($job);
			}
			catch(InvalidArgumentException $exception) {
				throw new ParseException("Error parsing cron: $line");
			}
		}
	}

	public function setRunCallback(callable $callback):void {
		$this->runCallback = $callback;
	}

	public function run(bool $stop = false):int {
		$this->stop = $stop;
		$jobsRan = 0;

		do {
			$this->queue->reset();
			$jobsRan += $this->queue->runDueJobs();

			if(is_callable($this->runCallback)) {
				call_user_func(
					$this->runCallback,
					$jobsRan,
					$this->queue->timeOfNextJob(),
					$stop
				);
			}

			if(!$this->stop) {
				sleep($this->queue->secondsUntilNextJob());
			}
		}
		while(!$this->stop);

		return $jobsRan;
	}
}