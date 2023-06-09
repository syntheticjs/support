To begin your Livewire journey, we will create a simple "counter" component and render it in the browser.

Most applications have no use for a "counter" component. Still, it's a great way to experience Livewire for the first time as it demonstrates Livewire's _liveness_ in the simplest way possible.

## Prerequisites

Before we start, make sure you have the following installed:

- Laravel version 10 or later
- PHP version 8.1 or later

## Install Livewire

From the root directory of your Laravel app, run the following [Composer](https://getcomposer.org/) command:

```shell
composer require livewire/livewire
```

## Create a Livewire component

Livewire provides a convenient Artisan command to generate new components quickly. Run the following command to make a new `Counter` component:

```shell
php artisan make:livewire counter
```

This command will generate two new files in your project:
* `app\Http\Livewire\Counter.php`
* `resources/views/livewire/counter.blade.php`

## Overwrite the class

Open `app/Http/Livewire/Counter.php` and replace its contents with the following:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 1; 

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Here's a brief explanation of the code above:
- `public $count = 1;` — Declares a public property called `$count` with an initial value of `1`.
- `public function increment()` — Declares a public method called `increment()` that increments the `$count` property each time it's called. Public methods like this can be triggered from the browser when, for example, a user clicks a button.
- `public function render()` — Declares a `render()` method that returns a Blade view. This Blade view will be used as the HTML template for our component.

## Overwrite the view

Open the `resources/views/livewire/counter.blade.php` file and replace its content with the following:  

```html
<div>
    <h1>{{ $count }}</h1>

    <button wire:click="increment">+</button>

    <button wire:click="decrement">-</button>
</div>
```

This code will display the `$count` property and two buttons that increment and decrement the `$count` property, respectively.

## Register a route for the component

Open the `routes/web.php` file in your Laravel application and add the following code:

```php
use App\Http\Livewire\Counter;

Route::get('/counter', Counter::class);
```

Now, our _counter_ component is assigned to the `/counter` route so that when a user visits the `/counter` endpoint in your application, this component will be used as the page.

## Create a template layout 

Before you can visit `/counter` in the browser, we need an HTML layout for our component to render inside. By default, Livewire will automatically look for a layout file called: `resources/views/components/layout.blade.php`

Create this file if it doesn't already exist by running the following command:

```shell
php artisan livewire:layout
```

This command will generate a file called `resources/views/components/layout.blade.php` with the following contents:

```html
<html>
    <head>
        <title>{{ $title ?? 'Page Title' }}</title>
    </head>
    
    <body>
    
    <!-- // -->
    
    {{ $slot }}
    
    </body>
</html>
```

The _counter_ component will be rendered in place of the `$slot` variable in the above template.

You may have noticed there is no JavaScript or CSS assets provided by Livewire. That is because version 3 and above automatically inject any frontend assets it needs.

## Test it out

Now that we're all set up, our component is ready to test!

Visit `/counter` in your browser, and you should see a number displayed on the screen with two buttons to increment and decrement the number.

After clicking one of the buttons, you will notice that the count updates in real time without the page reloading. This is the magic of Livewire: dynamic frontend applications written entirely in PHP.

We've barely scratched the surface of what Livewire is capable of. Keep reading the documentation to see all of what's available.