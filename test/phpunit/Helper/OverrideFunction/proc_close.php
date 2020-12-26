<?php
namespace Gt\Cron;

use Gt\Cron\phpunit\Helper\Override;

function proc_close() {
	Override::call("proc_close", func_get_args());
}