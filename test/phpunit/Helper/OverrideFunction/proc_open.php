<?php
namespace Gt\Cron;

use Gt\Cron\phpunit\Helper\Override;

function proc_open() {
	return Override::call("proc_open", func_get_args());
}