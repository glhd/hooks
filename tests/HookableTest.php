<?php

namespace Glhd\Hooks\Tests;

use Glhd\Hooks\Hookable;

class HookableTest extends TestCase
{
	public function test_breakpoints_are_fired_in_order(): void
	{
		$breakpoints = HookableTestObject::hook();
		
		// We'll intentionally register our hooks out of order so that we
		// know that registration order doesn't matter
		$breakpoints->afterSecond(fn() => hook_log('after second ran'));
		$breakpoints->afterFirst(fn() => hook_log('after first ran'));
		$breakpoints->beforeFirst(fn() => hook_log('before first ran'));
		$breakpoints->beforeSecond(fn() => hook_log('before second ran'));
		
		$obj = new HookableTestObject();
		$obj->first();
		$obj->second();
		
		$expected = [
			'before first ran',
			'first ran',
			'after first ran',
			'before second ran',
			'second ran',
			'after second ran',
		];
		
		$this->assertEquals($expected, hook_log()->all());
	}
}

class HookableTestObject
{
	use Hookable;
	
	public function first()
	{
		$this->breakpoint('beforeFirst');
		hook_log('first ran');
		$this->breakpoint('afterFirst');
	}
	
	public function second()
	{
		$this->breakpoint('beforeSecond');
		hook_log('second ran');
		$this->breakpoint('afterSecond');
	}
}
