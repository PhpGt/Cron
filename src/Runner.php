<?php
namespace Gt\Cron;

use Cron\CronExpression;
use InvalidArgumentException;

class Runner {
	protected $expressionList;
	public function __construct(string $contents) {
		$this->expressionList = [];

		foreach(explode("\n", $contents) as $line) {
			preg_match(
				"/(?P<crontab>\S+\s\S+\s\S+\s\S+\s\S+)\s(?P<command>.+)/",
				$line,
				$matches
			);

			$crontab = $matches["crontab"];
			$command = $matches["command"];

			try {
				$this->expressionList []= CronExpression::factory(
					$crontab
				);
			}
			catch(InvalidArgumentException $exception) {
				throw new ParseException("Error parsing cron: $line");
			}
		}
	}
}