<?php

namespace Glhd\Hooks\Tests;

use Glhd\Hooks\Hook;
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
	
	public function test_hooks_can_stop_propagation(): void
	{
		$breakpoints = HookableTestObject::hook();
		
		$breakpoints->beforeFirst(fn() => hook_log('before first 1'));
		$breakpoints->beforeFirst(function() {
			$this->stopPropagation();
			hook_log('before first 2');
		});
		$breakpoints->beforeFirst(fn() => hook_log('before first 3'));
		
		$obj = new HookableTestObject();
		$obj->first();
		
		$expected = [
			'before first 1',
			'before first 2',
			'first ran',
		];
		
		$this->assertEquals($expected, hook_log()->all());
	}
	
	public function test_view_hooks_can_be_registered(): void
	{
		// We'll intentionally register our hooks out of order so that we
		// know that registration order doesn't matter
		View::hook('demo', 'header', fn() => 'Hello Skyler!', Hook::LOW_PRIORITY);
		View::hook('demo', 'header', view('hello', ['name' => 'Bogdan']));
		View::hook('demo', 'footer', new HtmlString('Hello Chris!'));
		View::hook('demo', 'footer', view('hello', ['name' => 'Caleb']));
		
		$passed_args = null;
		View::hook('demo', 'footer', function($arguments) use (&$passed_args) {
			$passed_args = $arguments;
		});
		
		$view = $this->view('demo');
		
		$view->assertSeeTextInOrder([
			'Hello Bogdan!',
			'Hello Skyler!',
			'This is a demo',
			'Hello Chris!',
			'Hello Caleb!',
		]);
		
		$this->assertEquals(['foo' => 'bar'], $passed_args);
	}
}

class HookableTestObject
{
	use Hookable;
	
	public function first()
	{
		$this->callHook('beforeFirst');
		hook_log('first ran');
		$this->callHook('afterFirst');
	}
	
	public function second()
	{
		$this->callHook('beforeSecond');
		hook_log('second ran');
		$this->callHook('afterSecond');
	}
}
