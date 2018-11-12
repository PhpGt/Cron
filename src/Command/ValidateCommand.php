<?php
namespace Gt\Cron\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;

class ValidateCommand extends Command {
	public function __construct() {
		$this->setName("validate");
		$this->setDescription("Check the syntax of your crontab file and that the jobs exist");
	}

	public function run(ArgumentValueList $arguments):void {
		$this->stream->writeLine("VALIDATE RUN!");
	}
}