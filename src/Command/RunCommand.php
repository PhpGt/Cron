<?php

namespace Gt\Cron\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;

class RunCommand extends Command {
	public function __construct() {
		$this->setName("run");
		$this->setDescription("Start a long-running process to execute each job when it is due");
	}

	public function run(ArgumentValueList $arguments):void {
		$this->stream->writeLine("Running cron...");
	}
}