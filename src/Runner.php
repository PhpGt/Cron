<?php
namespace Gt\Cron;

use Cron\CronExpression;
use DateTime;
use InvalidArgumentException;

class Runner {
	/** @var bool */
	public $continue;
	/** @var Queue */
	protected $queue;
	/** @var callable */
	protected $runCallback;
	/** @var int */
	protected $numJobs;

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

		$this->numJobs = 0;

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

			$crontab = trim($crontab);
			$command = trim($command);

			if(strlen($crontab) > 0
			&& $crontab[0] === "#") {
				continue;
			}

			try {
				$job = call_user_func(
					[$jobRepository, "create"],
					CronExpression::factory($crontab),
					$command
				);
				$this->queue->add($job);
			}
			catch(InvalidArgumentException $exception) {
				throw new ParseException("Invalid syntax: $line");
			}

			$this->numJobs++;
		}
	}

	public function getNumJobs():int {
		return $this->numJobs;
	}

	public function setRunCallback(callable $callback):void {
		$this->runCallback = $callback;
	}

	public function run(bool $continue = false):int {
		$this->continue = $continue;

		do {
			$jobsRan = 0;
			$this->queue->reset();

			$jobsRan += $this->queue->runDueJobs();

			if(is_callable($this->runCallback)) {
				call_user_func(
					$this->runCallback,
					$jobsRan,
					$this->queue->timeOfNextJob(),
					$continue
				);
			}

			if($this->continue) {
				sleep($this->queue->secondsUntilNextJob());
			}
		}
		while($this->continue);

		return $jobsRan;
	}
}