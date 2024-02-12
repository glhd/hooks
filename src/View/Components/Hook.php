<?php

namespace Glhd\Hooks\View\Components;

use Glhd\Hooks\Support\HookRegistry;
use Glhd\Hooks\View\Observer;
use Illuminate\View\Component;

class Hook extends Component
{
	public function __construct(
		public HookRegistry $registry,
		public Observer $observer,
		public string $name,
		public ?string $view = null,
	) {
	}
	
	public function render()
	{
		return function(array $data) {
			$view = $this->view ?? $this->observer->active_view->getName();
			$attributes = $data['attributes']->getAttributes();
			
			return $this->registry
				->get($view)
				->run($this->name, $attributes)
				->map(fn($result) => e($result))
				->join('');
		};
	}
}
