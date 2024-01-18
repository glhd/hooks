<?php

use Glhd\Hooks\HookRegistry;

if (!function_exists('hook')) { // @codeCoverageIgnore
	function hook()
	{
		$registry = app(HookRegistry::class);
		
		if (func_num_args() === 0) {
			return $registry;
		}
		
		// FIXME
		throw new \Exception('TODO'); // $registry->make($view, $data, $mergeData);
	}
}
