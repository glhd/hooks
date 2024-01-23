<?php

namespace Glhd\Hooks;

use Closure;

class Hook
{
	public const LOW_PRIORITY = 1500;
	
	public const DEFAULT_PRIORITY = 1000;
	
	public const HIGH_PRIORITY = 500;
	
	public function __construct(
		public Closure $callback,
		public int $priority,
	) {
	}
	
	public function __invoke(array $arguments, Context $context): Context
	{
		return $context->addResult(call_user_func_array($this->callback, [...$arguments, $context]));
	}
}
