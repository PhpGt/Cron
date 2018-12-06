<?php
namespace Gt\Cron\Test\Command;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;

class CommandTestCase extends TestCase {
	protected $projectDirectory;

	public function setUp():void {
		$this->projectDirectory = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"cron",
			"test-project-" . uniqid(),
		]);
		mkdir(
			$this->projectDirectory,
			0775,
			true
		);
	}

	public function tearDown():void {
		$this->recursiveRemove($this->projectDirectory);
	}

	protected function recursiveRemove(string $path):void {
		if(is_dir($path)) {
			foreach(new DirectoryIterator($path) as $file) {
				if($file->isDot()) {
					continue;
				}

				$pathname = $file->getPathname();

				if(is_dir($pathname)) {
					$this->recursiveRemove($pathname);
				}
				else {
					unlink($pathname);
				}
			}
		}
		else {
			unlink($path);
		}
	}

	protected function writeCronContents(string $contents):void {
		file_put_contents(implode(DIRECTORY_SEPARATOR, [
			$this->projectDirectory,
			"crontab"
		]), $contents);
	}
}