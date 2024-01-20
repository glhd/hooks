<?php

namespace Glhd\Hooks\View;

use Illuminate\Support\Str;
use Illuminate\View\Factory;
use Illuminate\View\View;

class Observer
{
	public ?View $active_view = null;
	
	public function __construct(
		protected Factory $factory
	) {
	}
	
	public function observe(): void
	{
		$this->factory->composer('*', function(View $view) {
			if (! Str::startsWith($view->getName(), '__components::')) {
				$this->active_view = $view;
			}
		});
	}
}
