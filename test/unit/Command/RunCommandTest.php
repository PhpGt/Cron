<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\RunCommand;
use stdClass;

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

	public function testRunNow() {
		$mockBuilder = self::getMockBuilder(StdClass::class);
		$mockBuilder->setMethods(["doSomething"]);
		$mock = $mockBuilder->getMock();
		$mockClass = get_class($mock);

		$cronContents = <<<CRON
* * * * * $mockClass::doSomething
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);
		$command->run($args);

		self::assertStreamOutput("Just ran 1 job", $stream);
		self::assertStreamOutput("Stopping now", $stream);
	}
}