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

To make a class "hook-able" you need to use the `Hookable` trait:

```php
use Glhd\Hooks\Hookable;

class Session
{
    use Hookable;
    
    public function start() {
        $this->breakpoint('start'); // Code can now hook into this point in your code
        
        // ...
    }
}
```

To use these breakpoints, you must register your hooks:

```php
$breakpoints = Session::hook();
$breakpoints->start(fn() => Log::info('Session started'));
```

Now, whenenver `Session::start` is called, a `"Session started"` message will be logged!

## Within Views
