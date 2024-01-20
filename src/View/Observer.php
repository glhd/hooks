<?php

namespace Glhd\Hooks\View;

use Closure;
use Illuminate\Support\Str;
use Illuminate\View\Factory;
use Illuminate\View\View;

class Observer
{
	public ?View $active_view = null;
	
	protected bool $observing = true;
	
	public function __construct(
		protected Factory $factory
	) {
	}
	
	public function observe(): static
	{
		$this->factory->composer('*', function(View $view) {
			if ($this->observing && ! Str::startsWith($view->getName(), '__components::')) {
				$this->active_view = $view;
			}
		});
		
		return $this;
	}
	
	public function withoutObserving(Closure $callback)
	{
		$previously_observing = $this->observing;
		
		try {
			$this->observing = false;
			return $callback();
		} finally {
			$this->observing = $previously_observing;
		}
	}
	
	public function startObserving(): static
	{
		$this->observing = true;
		
		return $this;
	}
	
	public function stopObserving(): static
	{
		$this->observing = false;
		
		return $this;
	}
}
