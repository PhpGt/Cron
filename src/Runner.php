<?php
namespace Gt\Cron;

use Cron\CronExpression;
use DateTime;
use InvalidArgumentException;

class Runner {
	/** @var Queue */
	protected $queue;
	/** @var bool */
	public $stop;

	public function __construct(
		JobFactory $jobFactory,
		string $contents,
		DateTime &$now = null
	) {
		$this->jobList = [];
		$this->queue = new Queue($now);

		foreach(explode("\n", $contents) as $line) {
			preg_match(
				"/(?P<crontab>\S+\s\S+\s\S+\s\S+\s\S+)\s(?P<command>.+)/",
				$line,
				$matches
			);

			$crontab = $matches["crontab"];
			$command = $matches["command"];

			try {
				$job = JobFactory::create(
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

	public function run(bool $stop = false):int {
		$this->stop = $stop;
		$jobsRan = 0;

		do {
			$this->queue->reset();
			$jobsRan += $this->queue->runDueJobs();

			if(!$this->stop) {
				sleep($this->queue->secondsUntilNextJob());
			}
		}
		while(!$this->stop);

		return $jobsRan;
	}
}