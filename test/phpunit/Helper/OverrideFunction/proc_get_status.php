<?php
namespace Gt\Cron;

use Gt\Cron\Test\Helper\Override;

function proc_get_status() {
	Override::call("proc_get_status", func_get_args());
	return [
		"running" => false,
		"exitcode" => 0,
	];
}