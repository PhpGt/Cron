<?php
namespace Gt\Cron\Test\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cron\Command\ValidateCommand;
use Gt\Cron\ParseException;
use PHPUnit\Framework\TestCase;

class ValidateCommandTest extends CommandTestCase {
	public function testInvalid() {
		$cronContents = <<<CRON
* * This is wrong syntax
CRON;
		$this->writeCronContents($cronContents);

		self::expectException(ParseException::class);

		$cd = getcwd();
		chdir($this->projectDirectory);
		$command = new ValidateCommand();
		$command->run(new ArgumentValueList());
		chdir($cd);
	}
}