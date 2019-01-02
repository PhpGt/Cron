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
		$cronContents = <<<CRON
* * * * * \Gt\Cron\Test\Helper\ExampleClass::doSomething
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);

		self::assertEquals(
			0,
			\Gt\Cron\Test\Helper\ExampleClass::$calls
		);
		$command->run($args);
		self::assertEquals(
			1,
			\Gt\Cron\Test\Helper\ExampleClass::$calls
		);

		self::assertStreamOutput("Just ran 1 job", $stream);
		self::assertStreamOutput("Stopping now", $stream);
	}

	public function testRunNowWithArguments() {
		$cronContents = <<<CRON
* * * * * \Gt\Cron\Test\Helper\ExampleClass::doSomething("a test message", 123)
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);

		self::assertEquals(
			"",
			\Gt\Cron\Test\Helper\ExampleClass::$message
		);
		self::assertEquals(
			0,
			\Gt\Cron\Test\Helper\ExampleClass::$counter
		);
		$command->run($args);
		self::assertEquals(
			"a test message",
			\Gt\Cron\Test\Helper\ExampleClass::$message
		);
		self::assertEquals(
			123,
			\Gt\Cron\Test\Helper\ExampleClass::$counter
		);
	}
}