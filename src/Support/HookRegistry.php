<?php

namespace Glhd\Hooks\Support;

use Glhd\Hooks\Hook;
use Glhd\Hooks\Hooks;
use Illuminate\Support\Collection;

class HookRegistry
{
	/** @var Collection<\Glhd\Hooks\Hook>[] */
	protected array $hooks = [];
	
	public function register(Hook $hook, string $target, string $breakpoint = Hooks::DEFAULT): static
	{
		$registered = $this->initialize($target, $breakpoint);
		
		$registered->push($hook);
		
		$this->sortHooksByPriority($target, $breakpoint);
		
		return $this;
	}
	
	public function call(string $target, string $breakpoint, array $arguments): Collection
	{
		$results = new Collection();
		$registered = $this->initialize($target, $breakpoint);
		
		foreach ($registered as $hook) {
			$results->push($hook($arguments));
			
			if ($hook->should_stop_propagation) {
				break;
			}
		}
		
		return $results->filter();
	}
	
	/** @return Collection<\Glhd\Hooks\Hook> */
	protected function initialize(string $target, string $breakpoint): Collection
	{
		return $this->hooks[$target][$breakpoint] ??= new Collection();
	}
	
	protected function sortHooksByPriority(string $target, string $breakpoint): void
	{
		$prioritized = $this->hooks[$target][$breakpoint]->sortBy(fn(Hook $hook) => $hook->priority);
		
		$this->hooks[$target][$breakpoint] = $prioritized;
	}
}
