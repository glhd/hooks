<?php

namespace Glhd\Hooks\Support;

use Closure;
use Glhd\Hooks\Context;
use Glhd\Hooks\Hook;
use Glhd\Hooks\View\Components\Hook as HookComponent;
use Glhd\Hooks\View\Observer;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HooksServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('hooks')
			->setBasePath(dirname(__FILE__, 2))
			->hasConfigFile();
		
		$this->app->singleton(HookRegistry::class);
		$this->app->singleton(Observer::class);
		
		Blade::component('hook', HookComponent::class);
	}
	
	public function packageBooted()
	{
		$observer = $this->app->make(Observer::class)->observe();
		
		View::macro('hook', function(
			string $view,
			string $name,
			Closure|Htmlable|Hook $hook,
			int $priority = Hook::DEFAULT_PRIORITY
		) use ($observer) {
			$wrapper = function(Context $context) use ($observer, $hook) {
				// Unwrap the hook
				while ($hook instanceof Closure) {
					$hook = $hook($context);
				}
				
				// If it's a view, render it
				if ($hook instanceof Htmlable) {
					$hook = $observer->withoutObserving(function() use ($hook, $context) {
						if ($hook instanceof ViewContract) {
							$hook->with($context->data);
						}
						
						return new HtmlString($hook->toHtml());
					});
				}
				
				return $hook;
			};
			
			app(HookRegistry::class)
				->get($view)
				->on($name, $wrapper, $priority);
		});
	}
}
