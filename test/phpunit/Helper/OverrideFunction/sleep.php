<?php
namespace Gt\Cron;

use Gt\Cron\Test\Helper\Override;

function sleep() {
	Override::call("sleep", func_get_args());
}