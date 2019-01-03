<?php
namespace Gt\Cron;

use Gt\Cron\Test\Helper\Override;

function proc_open() {
	Override::call("proc_open", func_get_args());
}