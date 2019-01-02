<?php
namespace Gt\Cron;

use Gt\Cron\Test\Helper\Override;

function exec() {
	Override::call("exec", func_get_args());
}