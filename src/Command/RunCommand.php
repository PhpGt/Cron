<?php

namespace Gt\Cron\Command;

use DateTime;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;
use Gt\Cli\Stream;
use Gt\Cron\CronException;
use Gt\Cron\FunctionExecutionException;
use Gt\Cron\RunnerFactory;
use Gt\Cron\ScriptExecutionException;

class RunCommand extends Command {
	public function getName():string {
		return "run";
	}

	public function getDescription():string {
		return "Start a long-running process to execute each job when it is due";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(false, "once"),
		];
	}

	public function run(ArgumentValueList $arguments = null):void {
		try {
			$runner = RunnerFactory::createForProject(
				getcwd()
			);
		}
		catch(CronException $exception) {
			$this->output->writeLine(
				$exception->getMessage(),
				Stream::ERROR
			);
			return;
		}

		$once = $arguments->contains("once");

		$runner->setRunCallback([$this, "cronRunStep"]);

		try {
			$runner->run($once);
		}
		catch(ScriptExecutionException $exception) {
			$this->output->writeLine(
				"Error executing command: "
				. $exception->getMessage(),
				Stream::ERROR
			);
		}
		catch(FunctionExecutionException $exception) {
			$this->output->writeLine(
				"Error executing function: "
				. $exception->getMessage(),
				Stream::ERROR
			);
		}
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

		$this->output->writeLine(
			ucfirst($message)
		);
	}
}