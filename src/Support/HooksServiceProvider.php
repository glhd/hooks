<?php

namespace Glhd\Hooks\Support;

use Glhd\Hooks\HookRegistry;
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
	}
}
