<?php

namespace Glhd\Hooks;

use Closure;
use Illuminate\Support\Collection;

class Hooks
{
	public const DEFAULT = '__default__';
	
	protected array $hooks = [];
	
	public function __construct(
		public string $target,
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
		
		$this->hooks[$name][] = $hook;
		$this->sortHooksByPriority($name);
		
		return $this;
	}
	
	public function run(string $name, array $arguments): Collection
	{
		$results = new Collection();
		
		if (! isset($this->hooks[$name])) {
			return $results;
		}
		
		foreach ($this->hooks[$name] as $hook) {
			$results->push($hook($arguments));
			
			if ($hook->should_stop_propagation) {
				break;
			}
		}
		
		return $results->filter();
	}
	
	protected function sortHooksByPriority(string $breakpoint): void
	{
		usort($this->hooks[$breakpoint], static function(Hook $a, Hook $b) {
			return $a->priority <=> $b->priority;
		});
	}
}
