<?php

use Illuminate\Support\Collection;

function hook_log(?string $message = null): Collection
{
	if (! app()->has('glhd.hooks.log')) {
		app()->instance('glhd.hooks.log', new Collection());
	}
	
	$log = app('glhd.hooks.log');
	
	if ($message) {
		$log->push($message);
	}
	
	return $log;
}
