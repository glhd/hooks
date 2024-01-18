<?php

namespace Glhd\Hooks\Tests;

use Glhd\Hooks\Support\HooksServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	protected function setUp(): void
	{
		require_once __DIR__.'/helpers.php';
		
		parent::setUp();
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
