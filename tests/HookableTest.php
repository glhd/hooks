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
	
	public function test_hooks_are_fired_in_order(): void
	{
		$hooks = HookableTestObject::hook();
		
		// We'll intentionally register our hooks out of order so that we
		// know that registration order doesn't matter
		$hooks->afterSecond(fn() => hook_log('after second ran'));
		$hooks->afterFirst(fn() => hook_log('after first ran'));
		$hooks->beforeFirst(fn() => hook_log('before first ran'));
		$hooks->beforeSecond(fn() => hook_log('before second ran'));
		
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
	
	public function test_hooks_can_be_registered_with_on(): void
	{
		$hooks = HookableTestObject::hook();
		
		// We'll intentionally register our hooks out of order so that we
		// know that registration order doesn't matter
		$hooks->on('afterSecond', fn() => hook_log('after second ran'));
		$hooks->on('afterFirst', fn() => hook_log('after first ran'));
		$hooks->on('beforeFirst', fn() => hook_log('before first ran'));
		$hooks->on('beforeSecond', fn() => hook_log('before second ran'));
		
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
	
	public function test_an_object_can_have_a_default_hook(): void
	{
		HookableTestObject::hook(fn() => hook_log('default hook ran'));
		
		$obj = new HookableTestObject();
		$obj->first();
		$obj->second();
		
		$expected = [
			'default hook ran',
			'first ran',
			'default hook ran',
			'second ran',
		];
		
		$this->assertEquals($expected, hook_log()->all());
	}
	
	public function test_hooks_can_stop_propagation(): void
	{
		$hooks = HookableTestObject::hook();
		
		$hooks->beforeFirst(fn() => hook_log('before first 1'));
		$hooks->beforeFirst(function() {
			$this->stopPropagation();
			hook_log('before first 2');
		});
		$hooks->beforeFirst(fn() => hook_log('before first 3'));
		
		$obj = new HookableTestObject();
		$obj->first();
		
		$expected = [
			'before first 1',
			'before first 2',
			'first ran',
		];
		
		$this->assertEquals($expected, hook_log()->all());
	}
	
	public function test_data_can_be_passed_to_and_manipulated_by_hooks(): void
	{
		$obj = new HookableTestObject();
		$obj->withData('foo');
		
		$test = $this;
		
		HookableTestObject::hook()->on('withData', function() use ($test) {
			$test->assertEquals('bar', $this->value);
			$this->value = 'baz';
			
			return false;
		});
		
		$obj->withData('bar');
		
		$expected = [
			"got data: 'foo'",
			"got data: 'baz'",
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
		
		$test = $this;
		View::hook('demo', 'footer', function() use ($test) {
			$test->assertEquals('bar', $this->foo);
		});
		
		$view = $this->view('demo');
		
		$view->assertSeeTextInOrder([
			'Hello Bogdan!',
			'Hello Skyler!',
			'This is a demo',
			'Hello Chris!',
			'Hello Caleb!',
		]);
	}
}

class HookableTestObject
{
	use Hookable;
	
	public function first()
	{
		$this->callDefaultHook();
		$this->callHook('beforeFirst');
		hook_log('first ran');
		$this->callHook('afterFirst');
	}
	
	public function second()
	{
		$this->callDefaultHook();
		$this->callHook('beforeSecond');
		hook_log('second ran');
		$this->callHook('afterSecond');
	}
	
	public function withData(string $initial)
	{
		$result = $this->callHook('withData', value: $initial, continue: true);
		
		hook_log("got data: '{$result->value}'");
	}
}
