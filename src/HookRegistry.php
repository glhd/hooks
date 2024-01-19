<?php

namespace Glhd\Hooks;

use Illuminate\Support\Collection;

class HookRegistry
{
	/** @var Collection<\Glhd\Hooks\Hook>[] */
	protected array $hooks = [];
	
	public function registerListener(Hook $hook, string $target, string $breakpoint = Breakpoints::DEFAULT)
	{
		$registered = $this->initialize($target, $breakpoint);
		
		$registered->push($hook);
		
		$this->prioritize($target, $breakpoint);
	}
	
	public function call(string $target, string $breakpoint, array $arguments): Collection
	{
		$results = new Collection();
		$registered = $this->initialize($target, $breakpoint);
		
		foreach ($registered as $hook) {
			$results->push(call_user_func_array($hook->callback, $arguments));
		}
		
		return $results;
	}
	
	/** @return Collection<\Glhd\Hooks\Hook> */
	protected function initialize(string $target, string $breakpoint): Collection
	{
		return $this->hooks[$target][$breakpoint] ??= new Collection();
	}
	
	protected function prioritize(string $target, string $breakpoint): void
	{
		$existing = $this->hooks[$target][$breakpoint];
		
		$this->hooks[$target][$breakpoint] = $existing->sortBy(fn(Hook $hook) => $hook->priority);
	}
}
