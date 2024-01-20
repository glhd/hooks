<?php

namespace Glhd\Hooks\Support;

use Closure;
use Glhd\Hooks\Breakpoints;
use Glhd\Hooks\Hook;
use Glhd\Hooks\View\Components\Hook as HookComponent;
use Glhd\Hooks\View\Observer;
use Illuminate\Contracts\Support\Htmlable;
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
		$this->app->singleton(Observer::class);
		
		Blade::component('hook', HookComponent::class);
	}
	
	public function packageBooted()
	{
		$this->app->make(Observer::class)->observe();
		
		View::macro('hook', function(string $view, string $name, Closure|Htmlable|Hook $hook, int $priority = Hook::DEFAULT_PRIORITY) {
			if ($hook instanceof Htmlable) {
				$html = $hook->toHtml();
				$hook = fn() => new HtmlString($html);
			}
			
			$breakpoints = new Breakpoints($view, app(HookRegistry::class));
			$breakpoints->listen($name, $hook, $priority);
		});
	}
}
