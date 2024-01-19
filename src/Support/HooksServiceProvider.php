<?php

namespace Glhd\Hooks\Support;

use Closure;
use Glhd\Hooks\Breakpoints;
use Glhd\Hooks\Hook;
use Glhd\Hooks\HookRegistry;
use Glhd\Hooks\View\Components\Hook as HookComponent;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

// See: https://twitter.com/inxilpro/status/1722261847850848616
class HooksServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('hooks')
			->setBasePath(dirname(__FILE__, 2))
			->hasConfigFile();
		
		$this->app->singleton(HookRegistry::class);
		
		Blade::component('hook', HookComponent::class);
	}
	
	public function packageBooted()
	{
		View::macro('hook', function(string $name, Closure|ViewContract|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY) {
			if ($hook instanceof ViewContract) {
				$view = $hook;
				$hook = function(...$args) use ($view) {
					return new HtmlString($view->with($args)->render());	
				};
			}
			
			$breakpoints = new Breakpoints(View::class, app(HookRegistry::class));
			$breakpoints->listen($name, $hook, $priority);
		});
	}
}
