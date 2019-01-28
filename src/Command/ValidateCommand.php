<?php
namespace Gt\Cron\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Stream;
use Gt\Cron\CronException;
use Gt\Cron\RunnerFactory;

class ValidateCommand extends Command {
	public function __construct(Stream $output = null) {
		$this->setName("validate");
		$this->setDescription("Check the syntax of your crontab file and that the jobs exist");
		$this->setOutput($output);
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

		$this->output->writeLine("OK");
	}
}