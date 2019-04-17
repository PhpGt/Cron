<?php
namespace Gt\Cron\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;
use Gt\Cli\Stream;
use Gt\Cron\CronException;
use Gt\Cron\RunnerFactory;

class ValidateCommand extends Command {
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

		$this->output->writeLine("OK");
	}

	public function getName():string {
		return "validate";
	}

	public function getDescription():string {
		return "Check the syntax of your crontab file and that the jobs exist";
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
		return [];
	}
}