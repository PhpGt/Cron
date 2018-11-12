<?php
namespace Gt\Cron;

class RunnerFactory {
	public static function createForProject(
		string $projectDirectory
	):Runner {
		$crontabPath = implode(DIRECTORY_SEPARATOR, [
			$projectDirectory,
			"crontab",
		]);

		if(!is_file($crontabPath)) {
			throw new CrontabNotFoundException($crontabPath);
		}

		$jobFactory = new JobFactory();
		return new Runner(
			$jobFactory,
			file_get_contents($crontabPath)
		);
	}
}