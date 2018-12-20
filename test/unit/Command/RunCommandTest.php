<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\RunCommand;

class RunCommandTest extends CommandTestCase {
	public function testRunInvalidSyntax() {
		$cronContents = <<<CRON
* * This is wrong syntax
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);
		$command  = new RunCommand($stream);
		$command->run(new ArgumentValueList());

		self::assertStreamError(
			"Invalid syntax: * *",
			$stream
		);
	}
}