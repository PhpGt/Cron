<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Stream;
use Gt\Cron\Command\ValidateCommand;
use Gt\Cron\CrontabNotFoundException;
use Gt\Cron\ParseException;

class ValidateCommandTest extends CommandTestCase {
	public function testInvalid() {
		$cronContents = <<<CRON
* * This is wrong syntax
CRON;
		$this->writeCronContents($cronContents);

		$stream = $this->getStream();

		chdir($this->projectDirectory);
		$command = new ValidateCommand($stream);
		$command->run(new ArgumentValueList());

		self::assertStreamError(
			"Invalid syntax: * *",
			$stream
		);
	}

	public function testNotFound() {
		chdir($this->projectDirectory);

		$stream = $this->getStream();
		$command = new ValidateCommand($stream);
		$command->run(new ArgumentValueList());

		self::assertStreamError("crontab file not found at", $stream);
	}
}