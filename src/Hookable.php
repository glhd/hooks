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
	
	/**
	 * Trigger a hook within the object
	 *
	 * @param string $name
	 * @param ...$args
	 * @return \Illuminate\Support\Collection
	 */
	protected function callHook(string $name, ...$args): Collection
	{
		return app(HookRegistry::class)
			->get(static::class)
			->run($name, $args);
	}
	
	/**
	 * Call the "default" hook on this target
	 *
	 * @param ...$args
	 * @return \Illuminate\Support\Collection
	 */
	protected function callDefaultHook(...$args): Collection
	{
		return $this->callHook(Hooks::DEFAULT, ...$args);
	}
}
