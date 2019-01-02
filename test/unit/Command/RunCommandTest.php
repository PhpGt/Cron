<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\RunCommand;
use Gt\Cron\Test\Helper\Override;
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

	/** @runInSeparateProcess  */
	public function testRunNowFunction() {
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

	/** @runInSeparateProcess  */
	public function testRunNowFunctionWithArguments() {
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

	/** @runInSeparateProcess  */
	public function testRunNowFunctionNoSlash() {
		$cronContents = <<<CRON
* * * * * Gt\Cron\Test\Helper\ExampleClass::doSomething
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);
		$command->run($args);
		self::assertEquals(
			1,
			\Gt\Cron\Test\Helper\ExampleClass::$calls
		);
	}

	public function testRunNowScript() {
		$cronContents = <<<CRON
* * * * * /path/to/script/doSomething
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$calledCommand = null;

		Override::setCallback(
			"exec",
			function($command)use(&$calledCommand) {
				$calledCommand = $command;
			}
		);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);
		$command->run($args);
		self::assertEquals(
			"/path/to/script/doSomething",
			$calledCommand
		);
	}

	public function testRunNowScriptWithArguments() {
		$cronContents = <<<CRON
* * * * * /path/to/script/doSomething "a test message" 123
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$calledCommand = null;

		Override::setCallback(
			"exec",
			function($command)use(&$calledCommand) {
				$calledCommand = $command;
			}
		);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);
		$command->run($args);
		self::assertEquals(
			"/path/to/script/doSomething \"a test message\" 123",
			$calledCommand
		);
	}

	/** @runInSeparateProcess */
	public function testRunNowScriptAndFunction() {
		$cronContents = <<<CRON
* * * * * /path/to/script/doSomething "a test message" 123
* * * * * Gt\Cron\Test\Helper\ExampleClass::doSomething
CRON;
		$this->writeCronContents($cronContents);
		$stream = $this->getStream();
		chdir($this->projectDirectory);

		$calledCommand = null;

		Override::setCallback(
			"exec",
			function($command)use(&$calledCommand) {
				$calledCommand = $command;
			}
		);

		$args = new ArgumentValueList();
		$args->set("once");
		$command = new RunCommand($stream);
		$command->run($args);
		self::assertEquals(
			"/path/to/script/doSomething \"a test message\" 123",
			$calledCommand
		);
		self::assertEquals(
			1,
			\Gt\Cron\Test\Helper\ExampleClass::$calls
		);
	}

	public function testRunNowScriptNotExists() {
		$this->markTestSkipped("TODO");
	}

	public function testRunNowFunctionNotExists() {
		$this->markTestSkipped("TODO");
	}
}