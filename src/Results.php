<?php

namespace Glhd\Hooks;

use Illuminate\Support\Collection;
use OutOfBoundsException;

/** @mixin Collection */
class Results
{
	public bool $should_stop_propagation = false;
	
	protected array $results = [];
	
	public function __construct(
		public array $data = [],
	) {
	}
	
	public function __get(string $name)
	{
		if (! array_key_exists($name, $this->data)) {
			throw new OutOfBoundsException("No such result data exists: '{$name}'");
		}
		
		return $this->data[$name];
	}
	
	public function __set(string $name, $value): void
	{
		$this->data[$name] = $value;
	}

	public function __isset(string $name): bool
	{
		return isset($this->data[$name]);
	}
	
	public function __call(string $name, array $arguments)
	{
		return Collection::make($this->results)->{$name}(...$arguments);
	}
	
	public function addResult(mixed $result): static
	{
		$this->results[] = $result;
		
		return $this;
	}
	
	protected function stopPropagation(): void
	{
		$this->should_stop_propagation = true;
	}
}
