<?php

namespace Glhd\Hooks\Tests;

use Glhd\Hooks\Hookable;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class HookableTest extends TestCase
{
	use InteractsWithViews;
	
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
	
	public function test_view_hooks_can_be_registered(): void
	{
		// We'll intentionally register our hooks out of order so that we
		// know that registration order doesn't matter
		View::hook('view-hook-2', fn() => 'Hello Skyler!');
		View::hook('view-hook-2', view('hello', ['name' => 'Bogdan']));
		View::hook('view-hook-1', fn() => new HtmlString('Hello Chris!'));
		View::hook('view-hook-1', view('hello', ['name' => 'Caleb']));
		
		$view = $this->blade(<<<'blade'
		<div>
			<x-hook name="view-hook-1" />
			<x-hook name="view-hook-2" />
		</div>
		blade);
		
		$view->assertSeeTextInOrder([
			'Hello Chris!',
			'Hello Caleb!',
			'Hello Skyler!',
			'Hello Bogdan!',
		]);
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
