<?php

namespace Glhd\Hooks;

use Closure;

class Hook
{
	public const LOW_PRIORITY = 1500;
	
	public const DEFAULT_PRIORITY = 1000;
	
	public const HIGH_PRIORITY = 500;
	
	public bool $should_stop_propagation = false;
	
	public function __construct(
		public Closure $callback,
		public int $priority,
	) {
		// TODO: stop propagation?
	}
	
	public function __invoke(array $arguments)
	{
		return $this->callback->call($this, ...$arguments);
	}
	
	protected function stopPropagation(): void
	{
		$this->should_stop_propagation = true;
	}
}
