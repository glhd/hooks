<?php

namespace Glhd\Hooks\Support;

use Glhd\Hooks\Hook;
use Glhd\Hooks\Hooks;

class HookRegistry
{
	/** @var Hooks[] */
	protected array $hooks = [];
	
	public function get(string $target): Hooks
	{
		return $this->hooks[$target] ??= new Hooks($target);
	}
	
	public function register(Hook $hook, string $target, string $name = Hooks::DEFAULT): static
	{
		$this->get($target)->on($name, $hook);
		
		return $this;
	}
}
