<?php

namespace Glhd\Hooks\Support\Facades;

use Glhd\Hooks\HookRegistry;
use Illuminate\Support\Facades\Facade;

class Hook extends Facade
{
	/** @return HookRegistry */
	public static function getFacadeRoot()
	{
		return parent::getFacadeRoot();
	}
	
	protected static function getFacadeAccessor()
	{
		return HookRegistry::class;
	}
}
