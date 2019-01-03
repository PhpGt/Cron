<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\ValidateCommand;

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

	public function testRun() {
		chdir($this->projectDirectory);
		$this->writeCronContents("* * * * * Example::test");

		$stream = $this->getStream();
		$command = new ValidateCommand($stream);
		$command->run(new ArgumentValueList());

		self::assertStreamOutput("OK", $stream);
	}
}