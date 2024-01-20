<div style="float: right;">
	<a href="https://github.com/glhd/hooks/actions" target="_blank">
		<img 
			src="https://github.com/glhd/hooks/workflows/PHPUnit/badge.svg" 
			alt="Build Status" 
		/>
	</a>
	<a href="https://codeclimate.com/github/glhd/hooks/test_coverage" target="_blank">
		<img 
			src="https://api.codeclimate.com/v1/badges/change-me/test_coverage" 
			alt="Coverage Status" 
		/>
	</a>
	<a href="https://packagist.org/packages/glhd/hooks" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/hooks/v/stable" 
            alt="Latest Stable Release" 
        />
	</a>
	<a href="./LICENSE" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/hooks/license" 
            alt="MIT Licensed" 
        />
    </a>
    <a href="https://twitter.com/inxilpro" target="_blank">
        <img 
            src="https://img.shields.io/twitter/follow/inxilpro?style=social" 
            alt="Follow @inxilpro on Twitter" 
        />
    </a>
</div>

# Hooks

## Installation

```shell
composer require glhd/hooks
```

## Usage

The hooks package provides two types of hooks: hooking into class execution, and hooking into view rendering.

### Within Classes

To make a class "hook-able" you need to use the `Hookable` trait. In your code, you can add `callHook()`
calls anywhere that you want to allow outside code to execute. For example, if you were implementing
a `Session` class, you might want to allow code to hook into before the session starts, and before
the session saves:

```php
use Glhd\Hooks\Hookable;

class MySessionClass implements SessionHandlerInterface
{
    use Hookable;
    
    public function public open(string $path, string $name): bool
    {
        $this->callHook('beforeOpened', $name);
        // ...
    }
    
    public function write(string $id, string $data): bool
    {
        $this->callHook('beforeWritten');
        // ..
    }
}
```

Now, you can hook into these points from elsewhere in your app:

```php
// Get all the available hook points
$hooks = Session::hook();

// Register your custom code to execute at those points
$hooks->beforeOpened(fn($name) => Log::info("Starting session '$name'"));
$hooks->beforeWritten(fn() => Log::info('Writing session to storage'));
```

Now, whenenver `MySessionClass::open` is called, a "Starting session '<session name>'" message will be logged,
and whenever `MySessionClass::write` is called, a "Writing session to storage" message will be logged.

### Hook Priority

You can pass an additional `int` priority to your hooks, to account for multiple hooks
attached to the same point. For example:

```php
$hooks->beforeOpened(fn($name) => Log::info('Registered First'), 500);
$hooks->beforeOpened(fn($name) => Log::info('Registered Second'), 100);
```

Would cause "Registered Second" to log before "Registered First". If you don't pass a priority, the
default of `1000` will be used. All hooks at the same priority will be executed in the order they
were registered.

### When to use class hooks

Class hooks are mostly useful for package code that needs to be extensible without
knowing **how** it will exactly be extended. The Laravel framework provides similar extension
points, like [`Queue::createPayloadUsing`](https://github.com/laravel/framework/blob/443ec4438c48923c9caa9c2b409a12b84a10033f/src/Illuminate/Queue/Queue.php#L288).

In general, you should avoid using class hooks in your application code unless you are
dealing with particularly complex conditional logic that really warrants this approach.

## Within Views

Sometimes you may want to make certain views "hook-able" as well. For example, suppose
you have an ecommerce website that sends out email receipts, and you want to occasionally
add promotions or other contextual content to the email message. Rather than constantly
adding and removing a bunch of `@if` calls, you can use a hook:

```blade
{{-- emails/receipt.blade.php --}}
Thank you for shopping at…

<x-hook name="intro" />

Your receipt info…

<x-hook name="footer" />
```

Now you have two spots that you can hook into…

```php
// Somewhere in a `PromotionsServiceProvider` class, perhaps…

if ($this->isInCyberMondayPromotionalPeriod()) {
    View::hook('emails.receipt', 'intro', view('emails.promotions._cyber_monday_intro'));
}

if (Auth::user()->isNewRegistrant()) {
    View::hook('emails.receipt', 'footer', view('emails.promotions._thank_you_for_first_purchase'));
}
```

The `View::hook` method accepts 4 arguments. The first is the view name that you're
hooking into; the second is the name of the hook itself. The third argument can either
be a view (or anything that implements the `Htmlable` contract), or a closure that returns
anything that Blade can render. Finally, the fourth argument is a `priority` value—the lower
the priority, the earlier it will be rendered (if there are multiple things hooking into
the same spot). If you do not provide a priority, it will be set the `1000` by default.
