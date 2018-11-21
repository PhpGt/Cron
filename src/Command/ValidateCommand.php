<?php
namespace Gt\Cron\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Stream;
use Gt\Cron\CronException;
use Gt\Cron\RunnerFactory;

class ValidateCommand extends Command {
	public function __construct() {
		$this->setName("validate");
		$this->setDescription("Check the syntax of your crontab file and that the jobs exist");
	}

	public function run(ArgumentValueList $arguments):void {
		try {
			$runner = RunnerFactory::createForProject(
				getcwd()
			);
		}
		catch(CronException $exception) {
			$this->stream->writeLine(
				$exception->getMessage(),
				Stream::ERROR
			);
			exit(1);
		}

		$this->stream->writeLine("OK");
	}
}