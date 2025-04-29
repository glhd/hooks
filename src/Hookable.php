<?php

namespace Glhd\Hooks;

use BackedEnum;
use Closure;
use Glhd\Hooks\Support\HookRegistry;
use TypeError;

trait Hookable
{
	/**
	 * Hook into this class
	 *
	 * @param \Closure|\Glhd\Hooks\Hook|null $callback Optional callback for the "default" hook
	 * @param int $priority Hook priority (lower is higher priority)
	 * @return \Glhd\Hooks\Hooks
	 */
	public static function hook(Closure|Hook|null $callback = null, int $priority = Hook::DEFAULT_PRIORITY): Hooks
	{
		$hooks = app(HookRegistry::class)->get(static::class);
		
		if ($callback) {
			$hooks->default($callback, $priority);
		}
		
		return $hooks;
	}
	
	protected function callHook(BackedEnum|string $name, ...$args): Context
	{
		if ($name instanceof BackedEnum) {
			if (! is_string($name->value)) {
				throw new TypeError('Name must be either a string or an enum backed by a string');
			}
			
			$name = $name->value;
		}
		
		return app(HookRegistry::class)
			->get(static::class)
			->run($name, $args);
	}
	
	protected function callDefaultHook(...$args): Context
	{
		return $this->callHook(Hooks::DEFAULT, ...$args);
	}
}
