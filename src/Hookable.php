<?php

namespace Glhd\Hooks;

use Closure;

trait Hookable
{
	public static function hook(Closure|Hook|null $callback = null, int $priority = Hook::DEFAULT_PRIORITY): Breakpoints
	{
		$breakpoints = new Breakpoints(static::class, app(HookRegistry::class));
		
		if ($callback) {
			$breakpoints->default($callback, $priority);
		}
		
		return $breakpoints;
	}
	
	protected function breakpoint(string $name, ...$args)
	{
		app(HookRegistry::class)->call(static::class, $name, $args);
	}
}
