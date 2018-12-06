<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\ValidateCommand;
use Gt\Cron\CrontabNotFoundException;
use Gt\Cron\ParseException;

class ValidateCommandTest extends CommandTestCase {
	public function testInvalid() {
		$cronContents = <<<CRON
* * This is wrong syntax
CRON;
		$this->writeCronContents($cronContents);

		self::expectException(ParseException::class);

		chdir($this->projectDirectory);
		$command = new ValidateCommand();
		$command->run(new ArgumentValueList());
	}

	public function testNotFound() {
		self::expectException(CrontabNotFoundException::class);
		chdir($this->projectDirectory);
		$command = new ValidateCommand();
		$command->run(new ArgumentValueList());
	}
}