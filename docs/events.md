Livewire offers a robust event system that you can use to communicate between different components on the page. Because it uses browser events under the hood, you can also use it to communicate with Alpine components or even plain, vanilla JavaScript.

To trigger an event, you can use the `dispatch()` method from anywhere inside your component and listen for that event from any other component on the page.

# Dispatching events

To dispatch an event from a Livewire component, you can call the `dispatch()` method, passing it the event name and any additional data you want to send along with the event.

Here's an example of dispatching a "post-created" event from a `CreatePost` component:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created'); // [tl! highlight]
    }
}
```

In this example, when the `dispatch()` method is called, the `post-created` event will be dispatched, and every other component listening on the page will be notified.

You can also pass additional data along with the event by passing it as the second parameter:

```php
$this->dispatch('post-created', title: $post->title);
```


# Listening for events

To listen for an event in a Livewire component, add the `#[On]` attribute with the event name above the method you want to be called with the event is dispatched.

```php
use Livewire\Component;

class Dashboard extends Component
{
	#[On('post-created')] // [tl! highlight]
    public function updatePostList($title)
    {
		// ...
    }
}
```

When the `post-created` event is dispatched from `CreatePost`, this component will pick it up, a network request will be triggered, and the `notifyAboutNewPost()` action will be run.

Notice additional data sent along with the event will be passed through as the first parameter to the `notifyAboutNewPost()` method.

# Events in Alpine

Because Livewire events are plain browser events under the hood, you can use Alpine to listen for them or even dispatch them.

## Listening for Livewire events in Alpine

To listen for a `post-created` event from Alpine, you would do something like the following:

```html
<div x-on:post-created="..."></div>
```

The above snippet would listen for a Livewire component that dispatched the `post-created` event.

It's important to note that it will respond to this event if the Livewire component is a child of this element.

To listen for any Livewire component on the page dispatching `post-created`, you can add `.window` to the listener to listen globally:

```html
<div x-on:post-created.window="..."></div>
```

If you want to access additional data sent along with the event, you can do so using `$event.detail`:

```html
<div x-on:post-created="notify('New post: ' + $event.detail.title)"></div>
```

You can read more about [listening for events in Alpine here.](https://alpinejs.dev/directives/on)

## Dispatching Livewire events from Alpine

Any event dispatched from Alpine is capable of being picked up by a Livewire component.

Let's look at what it would look like to dispatch the `post-created` event from Alpine itself:

```html
<button @click="$dispatch('post-created')">...</button>
```

Just like the Livewire method, you can pass additional data along with the event by passing it as the second parameter:

```html
<button @click="$dispatch('post-created', { title: 'Post Title' })">...</button>
```

You can read more about [dispatching events in Alpine here.](https://alpinejs.dev/magics/dispatch)

## Listening for events from children only

By default, when you register a Livewire event listener using `#[On]`, it will listen for that event to be dispatched anywhere on the page—It does this by listening for the event on the `window` object.

Sometimes you may want to scope an event listener to only listen for event dispatches from child components rendered somewhere within the listening component.

To listen for children dispatches only, you can pass a second argument to `#[On]` called `fromChildren` and set it to true:

```php
use Livewire\Component;

class Dashboard extends Component
{
	#[On('post-created', fromChildren: true)] // [tl! highlight]
    public function updatePostCount()
    {
		// ...
    }
}
```

The `updatePostCount()` method will only be triggered when a child component dispatches a `post-created` event.

> [!tip] You might not need events
> If you are using events to call behavior on a parent from a child directly, you can instead call the action directly from the child using `$parent` in your Blade template. For example:
> 
> ```html
> <button wire:click="$parent.showCreatePostForm()">Create Post</button>
> ```
>
> [Read more about using $parent here](/docs/nesting#directly-accessing-the-parent-from-the-child).

## Dispatching directly to another component

If you want to use events for communicating directly between two components on the page, you can use the `dispatch()->to()` modifier.

Below is an example of the `CreatePost` component dispatching the `post-created` event directly to the `Dashboard` component, skipping any other components listening for that specific event:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created')->to(Dashboard::class);
    }
}
```

## Dispatching a component event to itself

You can restrict an event to only dispatching on the component it was triggered from like so:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created')->self();
    }
}
```

Now, the above component is both dispatching and listening for `post-created` on itself.

## Dispatching events from Blade Templates

You can dispatch events directly from your Blade templates using the `$dispatch` JavaScript function. This is useful when you want to trigger an event from a user interaction, such as a button click:

```html
<button wire:click="$dispatch('show-post-modal', { id: {{ $post->id }} })">
    EditPost
</button>
```

In this example, when the button is clicked, the `show-post-modal` event will be emitted with the specified data.

## Testing dispatched events

To test events emitted by your component, use the `assertDispatched()` method in your Livewire test. This method checks that a specific event has been dispatched during the component's lifecycle:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Livewire\CreatePost;
use Livewire\Livewire;

class CreatePostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_post_created_event()
    {
        Livewire::test(CreatePost::class)
            ->call('save')
            ->assertDispatched('post-created');
    }
}
```

In this example, the test ensures that the `post-created` event is dispatched with the specified data when the `save()` method is called on the `CreatePost` component.

### Testing Event Listeners

To test event listeners, you can emit events from the test environment and assert that the expected actions are performed in response to the event:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Livewire\Dashboard;
use Livewire\Livewire;

class DashbaordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_post_count_when_a_post_is_created()
    {
        Livewire::test(Dashboard::class)
            ->assertSee('Posts created: 0')
            ->dispatch('post-created')
            ->assertSee('Posts created: 1');
    }
}
```

In this example, the test dispatches the `post-created` event, then checks that the `Dashboard` component properly handles the event and displays the updated count.

## Real-time events using Laravel Echo

Livewire pairs nicely with [Laravel Echo](https://laravel.com/docs/10.x/broadcasting#client-side-installation) to provide real-time functionality on your web-pages using WebSockets.

> [!warning] Installing Laravel Echo is a prerequisite
> This feature assumes you have installed Laravel Echo and the `window.Echo` object is globally available in your application. For more information on installing echo, check out the [Laravel Echo documentation](https://laravel.com/docs/10.x/broadcasting#client-side-installation).

### Listening for Echo events

Let's say you have an event in your Laravel application called `OrderShipped`:

```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public Order $order;

    public function broadcastOn()
    {
        return new Channel('orders');
    }
}
```

You might dispatch this event from another part of your application like so:

```php
use App\Events\OrderShipped;

OrderShipped::dispatch();
```

If you were to listen for this event in JavaScript using only Laravel Echo, it would look something like this:

```js
Echo.channel('orders')
    .listen('OrderShipped', e => {
        console.log(e.order)
    })
```

Assuming you have Laravel Echo installed and configured, you can listen for this event from inside a Livewire component.

Below is an example of a component called `OrderTracker` that is listening for the `OrderShipped` event to show users a visual indication of a new order:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class OrderTracker extends Component
{
    public $showNewOrderNotification = false;

    #[On('echo:orders,OrderShipped')]
    public function notifyNewOrder()
    {
        $this->showNewOrderNotification = true;
    }

    // ...
}
```

If you have Echo channels with variables embedded in it (such as a Order ID) you can use the `getListeners()` function instead of the `#[On]` attribute:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Prop;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Order;

class OrderTracker extends Component
{
    #[Prop]
    public Order $order;

    public $showOrderShippedNotification = false;

    public function getListeners()
    {
        return [
            "echo:orders.{$this->order->id},OrderShipped" => 'notifyShipped',
        ];
    }

    #[On('echo:orders,OrderShipped')]
    public function notifyShipped()
    {
        $this->showOrderShippedNotification = true;
    }

    // ...
}
```

Now, Livewire will intercept the received event from Echo, and act accordingly.

### Private & presence channels

In a similar way to regular public channels, you can also listen to events broadcasted to private and presence channels:

> [!info]
> Make sure you have your <a href="https://laravel.com/docs/master/broadcasting#defining-authorization-callbacks">Authentication Callbacks</a> properly defined.

```php
<?php

namespace App\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class OrderTracker extends Component
{
    public $showNewOrderNotification = false;

    public function getListeners()
    {
        return [
            // Public Channel
            "echo:orders,OrderShipped" => 'notifyNewOrder',
            
            // Private Channel
            "echo-private:orders,OrderShipped" => 'notifyNewOrder',
            
            // Presence Channel
            "echo-presence:orders,OrderShipped" => 'notifyNewOrder',
            "echo-presence:orders,here" => 'notifyNewOrder',
            "echo-presence:orders,joining" => 'notifyNewOrder',
            "echo-presence:orders,leaving" => 'notifyNewOrder',
        ];
    }

    public function notifyNewOrder()
    {
        $this->showNewOrderNotification = true;
    }
}
```