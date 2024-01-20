<?php

namespace Glhd\Hooks;

use Closure;
use Glhd\Hooks\Support\HookRegistry;
use Illuminate\Support\Collection;

trait Hookable
{
	/**
	 * Hook into this class
	 *
	 * @param \Closure|\Glhd\Hooks\Hook|null $callback Optional callback for the "default" breakpoint
	 * @param int $priority Hook priority (lower is higher priority)
	 * @return \Glhd\Hooks\Breakpoints
	 */
	public static function hook(Closure|Hook|null $callback = null, int $priority = Hook::DEFAULT_PRIORITY): Breakpoints
	{
		$breakpoints = new Breakpoints(static::class, app(HookRegistry::class));
		
		if ($callback) {
			$breakpoints->default($callback, $priority);
		}
		
		return $breakpoints;
	}
	
	/**
	 * Trigger a "hook-able" breakpoint within the object
	 *
	 * @param string $name
	 * @param ...$args
	 * @return \Illuminate\Support\Collection
	 */
	protected function callHook(string $name, ...$args): Collection
	{
		return app(HookRegistry::class)->call(static::class, $name, $args);
	}
}
