<?php
namespace Gt\Cron;

use Gt\Cron\phpunit\Helper\Override;

function sleep() {
	Override::call("sleep", func_get_args());
}