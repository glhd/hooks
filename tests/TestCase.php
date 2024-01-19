<?php

namespace Glhd\Hooks\Tests;

use Glhd\Hooks\Support\HooksServiceProvider;
use Illuminate\View\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	protected function setUp(): void
	{
		parent::setUp();
		
		require_once __DIR__.'/helpers.php';
		
		$this->app->make(Factory::class)->addLocation(__DIR__.'/views');
	}
	
	protected function getPackageProviders($app)
	{
		return [
			HooksServiceProvider::class,
		];
	}
	
	protected function getPackageAliases($app)
	{
		return [];
	}
	
	protected function getApplicationTimezone($app)
	{
		return 'America/New_York';
	}
}
