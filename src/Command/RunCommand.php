<?php

namespace Gt\Cron\Command;

use DateTime;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cron\RunnerFactory;

class RunCommand extends Command {
	public function __construct() {
		$this->setName("run");
		$this->setDescription("Start a long-running process to execute each job when it is due");
	}

	public function run(ArgumentValueList $arguments):void {
		$runner = RunnerFactory::createForProject(
			getcwd()
		);
		$runner->setRunCallback([$this, "cronRunStep"]);
		$runner->run();
	}

	public function cronRunStep(
		int $jobsRan,
		DateTime $wait,
		bool $stop
	) {
		$message = "";
		$now = new DateTime();

		$jobPlural = "job";
		if($jobsRan !== 1) {
			$jobPlural .= "s";
		}

		if($jobsRan > 0) {
			$message = "Just ran $jobsRan $jobPlural, ";
		}

		$message .= "next job at: " . $wait->format("H:i:s");

		if($now->diff($wait)->format("%a") > 0) {
			$message .= " on " . $wait->format("dS M");
		}
		if($now->diff($wait)->format("%y") > 0) {
			$message .= " " . $wait->format("Y");
		}

		if($stop) {
			$message .= ". Stopping now.";
		}
		else {
			$message .= ". Waiting...";
		}

		$this->stream->writeLine(
			ucfirst($message)
		);
	}
}