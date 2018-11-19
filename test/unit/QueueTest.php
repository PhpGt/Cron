<?php
namespace Gt\Cron\Test;

use Gt\Cron\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase {
	public function testGetNextJobEmpty() {
		$queue = new Queue();
		self::assertNull($queue->getNextJob());
	}
}