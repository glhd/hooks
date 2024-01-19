<?php

namespace Glhd\Hooks\View\Components;

use Glhd\Hooks\HookRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Hook extends Component
{
	public function __construct(
		public HookRegistry $registry,
		public string $name,
	) {
	}
	
	public function render()
	{
		return function(array $data) {
			return $this->registry->call(View::class, $this->name, $data['attributes']->getAttributes())
				->filter()
				->map(fn($result) => e($result))
				->join('');
		};
	}
}
