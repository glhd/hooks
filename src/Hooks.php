<?php

namespace Glhd\Hooks;

use Closure;
use Glhd\Hooks\Support\HookRegistry;

class Hooks
{
	public const DEFAULT = '__default__';
	
	public function __construct(
		public string $target,
		public HookRegistry $registry,
	) {
	}
	
	public function __call(string $name, array $arguments): static
	{
		$hook = $arguments[0];
		$priority = $arguments[1] ?? Hook::DEFAULT_PRIORITY;
		
		$this->on($name, $hook, $priority);
		
		return $this;
	}
	
	public function default(Closure|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY): static
	{
		$this->on(static::DEFAULT, $hook, $priority);
		
		return $this;
	}
	
	public function on(string $name, Closure|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY): static
	{
		if ($hook instanceof Closure) {
			$hook = new Hook($hook, $priority);
		}
		
		$this->registry->register($hook, $this->target, $name);
		
		return $this;
	}
}
