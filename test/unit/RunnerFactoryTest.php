<?php
namespace Gt\Cron\Test;

use Gt\Cron\CrontabNotFoundException;
use Gt\Cron\RunnerFactory;
use PHPUnit\Framework\TestCase;

class RunnerFactoryTest extends TestCase {
	public function testCreateForProjectNoCrontabFile() {
		$dir = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"cron",
			"example-project-" . uniqid()
		]);
		mkdir($dir, 0775, true);
		self::expectException(CrontabNotFoundException::class);
		RunnerFactory::createForProject($dir);
	}
}