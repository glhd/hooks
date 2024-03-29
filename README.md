<div style="float: right;">
	<a href="https://github.com/glhd/hooks/actions" target="_blank">
		<img 
			src="https://github.com/glhd/hooks/workflows/PHPUnit/badge.svg" 
			alt="Build Status" 
		/>
	</a>
	<a href="https://codeclimate.com/github/glhd/hooks/test_coverage" target="_blank">
		<img 
			src="https://api.codeclimate.com/v1/badges/043ef16ea1d5337d2558/test_coverage" 
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
$hooks->beforeOpened(function($name) {
    Log::info("Starting session '$name'");
});

$hooks->beforeWritten(function() {
    Log::info('Writing session to storage');
});
```

Now, whenever `MySessionClass::open` is called, a `"Starting session '<session name>'"` message will be logged,
and whenever `MySessionClass::write` is called, a `"Writing session to storage"` message will be logged.

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

### Stopping Propagation

Hooks can halt further hooks from running with a special `stopPropagation` call (just like JavaScript).
All hooks receive a `Context` object as the last argument. Calling `stopPropagation` on this object
will halt any future hooks from running:

```php
use Glhd\Hooks\Context;

$hooks->beforeOpened(function($name) {
    Log::info('Lower-priority hook');
}, 500);

$hooks->beforeOpened(function($name, Context $context) {
    Log::info('Higher-priority hook');
    $context->stopPropagation();
}, 100);
```

In the above case, the `'Lower-priority hook'` message will never be logged, because a higher-priority
hook stopped propagation before it could run.

### Passing data between your code and hooks

There are three different ways that data gets passed in and out of hooks:

1. Passing arguments *into* hooks (one-way)
2. Returning values *from* hooks (one-way)
3. Passing data into hooks that can be mutated by hooks (two-way)

#### One-way data

Options 1 and 2 are relatively simple. Any positional argument that you pass to `callHook` will
be forwarded to the hook as-is. In our example above, the `beforeOpened` call passed `$name` to
its hooks, and our hook accepted `$name` as its first argument.

A collection of returned values from our hooks is available to the calling code. For example,
if we wanted to allow hooks to add extra recipients to all email sent by our `Mailer` class,
we might do something like:

```php
use Glhd\Hooks\Hookable;

class Mailer
{
    use Hookable;
    
    protected function setRecipients() {
        $recipients = $this->callHook('preparingRecipients')
            ->filter()
            ->append($this->to);
            
        $this->service->setTo($recipients);
    }
}
```

```php
// Always add QA to recipient list in staging
if (App::environment('staging')) {
    Mailer::hook()->preparingRecipients(fn() => 'qa@myapp.com');
}
```

It's important to note that you will **always** get a collection of results, though, even
if there is only one hook attached to a call, because you never know how many hooks may
be registered.

#### Two-way data

Sometimes you need your calling code and hooks to pass the same data in two directions. A
common use-case for this is when you want your hooks to have the option to abort execution,
or change some default behavior. You can do this by passing named arguments to the call,
which will be added to the `Context` object that is passed as the last argument to your hook.

For example, what if we want hooks to have the ability to *prevent* mail from sending at all?
We might do that with something like:

```php
use Glhd\Hooks\Hookable;

class Mailer
{
    use Hookable;
    
    protected function send() {
        $result = $this->callHook('beforeSend', $this->message, shouldSend: true);
        
        if ($result->shouldSend) {
            $this->service->send();
        }
    }
}
```

```php
// Never send mail to mailinator addresses
Mailer::hook()->beforeSend(function($message, $context) {
    if (str_contains($message->to, '@mailinator.com')) {
        $context->shouldSend = false;
    }
});
```

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
    View::hook('emails.receipt', 'intro', fn() => view('emails.promotions._cyber_monday_intro'));
}

if (Auth::user()->isNewRegistrant()) {
    View::hook('emails.receipt', 'footer', fn() => view('emails.promotions._thank_you_for_first_purchase'));
}
```

The `View::hook` method accepts 4 arguments. The first is the view name that you're
hooking into; the second is the name of the hook itself. The third argument can either
be a view (or anything that implements the `Htmlable` contract), or a closure that returns
anything that Blade can render. Finally, the fourth argument is a `priority` value—the lower
the priority, the earlier it will be rendered (if there are multiple things hooking into
the same spot). If you do not provide a priority, it will be set the `1000` by default.

### Explicitly Setting View Name

The `<x-hook>` Blade component can usually infer what view it's being rendered inside. 
Depending on how your views are rendered, though, you may need to explicitly pass the view
name to the component. You can do that by passing an additional `view` prop:

```blade
<x-hook view="emails.receipt" name="intro" />
```

This is a requirement that we hope to improve in a future release!

### View Hook Attributes

It's possible to pass component attributes to your hooks, using regular Blade syntax:

```blade
<x-hook name="status" status="Demoing hooks" />
```

Your hooks will then receive the `status` value (and any other attributes you pass):

```php
View::hook('my.view', 'status', function($attributes) {
    assert($attributes['status'] === 'Demoing hooks');
});
```

If you pass the hook a Laravel view, any attributes will automatically be forwarded.
This means that you can use the `$status` variable inside your view. For example,
given the following views:

```blade
{{-- my/view.blade.php --}}
<x-hook name="status" status="Demoing hooks" />

{{-- my/hook.blade.php --}}
<div class="alert">
    Your current status is '{{ $status }}'
</div>
```

The following hook code would automatically forward the value `"Demoing hooks"` as
the `$status` attribute in your `my.hook` view:

```php
View::hook('my.view', 'status', view('my.hook'));
```
