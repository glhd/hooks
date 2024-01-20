<?php

namespace Glhd\Hooks;

use Closure;
use Glhd\Hooks\Support\HookRegistry;

class Breakpoints
{
	public const DEFAULT = '__default__';
	
	public function __construct(
		public string $target,
		public HookRegistry $registry,
	) {
	}
	
	public function __call(string $name, array $arguments)
	{
		$hook = $arguments[0];
		$priority = $arguments[1] ?? Hook::DEFAULT_PRIORITY;
		
		$this->listen($name, $hook, $priority);
	}
	
	public function default(Closure|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY)
	{
		$this->listen(static::DEFAULT, $hook, $priority);
	}
	
	public function listen(string $name, Closure|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY)
	{
		if ($hook instanceof Closure) {
			$hook = new Hook($hook, $priority);
		}
		
		$this->registry->register($hook, $this->target, $name);
	}
}
