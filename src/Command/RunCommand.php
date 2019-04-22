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
	public function run(ArgumentValueList $arguments = null):void {
		try {
			$runner = RunnerFactory::createForProject(
				getcwd(),
				$arguments->get("file", "crontab")
			);
		}
		catch(CronException $exception) {
			$this->output->writeLine(
				$exception->getMessage(),
				Stream::ERROR
			);
			return;
		}

		$runner->setRunCallback([$this, "cronRunStep"]);

		try {
			$runner->run($arguments->contains("watch"));
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
		bool $continue
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

		if($continue) {
			$message .= ". Waiting...";
		}
		else {
			$message .= ". Stopping now.";
		}

		$this->output->writeLine(
			ucfirst($message)
		);
	}

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
		return [
			new NamedParameter("file"),
		];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return[];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(
				false,
				"once",
				null,
				"Pass this flag to only run commands that are due at the time of running. Without this flag, cron will continue to run forever, executing tasks when they become ready."
			),
		];
	}
}