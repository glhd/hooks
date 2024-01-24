<?php

namespace Glhd\Hooks;

use Closure;

class Hooks
{
	public const DEFAULT = '__default__';
	
	/** @var array<string,\Glhd\Hooks\Hook> */
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
	
	public function run(string $name, array $arguments): Context
	{
		[$arguments, $data] = $this->partition($arguments);
		
		$context = new Context($data);
		
		foreach ($this->getHooks($name) as $hook) {
			$context->addResult($hook([...$arguments, $context]));
			
			if ($context->should_stop_propagation) {
				break;
			}
		}
		
		return $context;
	}
	
	protected function partition(array $arguments): array
	{
		$positional = [];
		$named = [];
		
		foreach ($arguments as $key => $value) {
			if (is_int($key)) {
				$positional[] = $value;
			} else {
				$named[$key] = $value;
			}
		}
		
		return [$positional, $named];
	}
	
	protected function sortHooksByPriority(string $name): void
	{
		usort($this->hooks[$name], static function(Hook $a, Hook $b) {
			return $a->priority <=> $b->priority;
		});
	}
	
	/** @return array<string, \Glhd\Hooks\Hook> */
	protected function getHooks(string $name): array
	{
		return $this->hooks[$name] ?? [];
	}
}
