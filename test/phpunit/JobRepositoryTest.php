<?php
namespace Gt\Cron\phpunit;

use Cron\CronExpression;
use Gt\Cron\Job;
use Gt\Cron\JobRepository;
use PHPUnit\Framework\TestCase;

class JobRepositoryTest extends TestCase {
	public function testCreate() {
		$command = uniqid();

		$repository = new JobRepository();
		$job = $repository->create(
			$this->mockExpression(),
			$command
		);

		self::assertInstanceOf(Job::class, $job);
		self::assertEquals($command, $job->getCommand());
	}

	protected function mockExpression():CronExpression {
		$expression = self::createMock(CronExpression::class);
		/** @var CronExpression $expression */
		return $expression;
	}
}