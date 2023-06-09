After a user performs some action—like submiting a form—you may want to redirect them to another page in your app.

Because Livewire requests aren't standard full-page browser requests, standard HTTP redirects won't work. You need to trigger redirects via JavaScript. Fortunately, Livewire hides this complexity by allowing you to use [Laravel's built-in redirect utilties](https://laravel.com/docs/10.x/responses#redirects) within your components, and internally, it will handle the process of redirecting on the frontend.

Here's an example of a `CreatePost` Livewire component that redirects the user after they submit the form:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
		Post::create([
			'title' => $this->title,
			'content' => $this->content,
		]);

		return redirect()->to('/posts'); // [tl! highlight]
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

As you can see, when the `save` action is triggered, a redirect will also be triggered to `/posts`. When Livewire receives this response, it will redirect the user on the frontend to the new URL.

## Redirecting to full-page components

Because Livewire uses Laravel's built-in redirector, you can use all the available methods.

If you are using a Livewire component as a full-page component for a route like so:

```php
use App\Http\Livewire\ShowPosts;

Route::get('/posts', ShowPosts::class);
```

You can redirect there by referencing the Livewire component directly instead of the route name or URL:

```php
public function save()
{
    // ...

    return redirect()->action(ShowPage::class);
}
```

This works because Livewire components are also [single-action controllers](https://laravel.com/docs/10.x/controllers#single-action-controllers) under the hood, so Laravel's native `->action()` redirector method will work seamlessly.

## Flash messages

In addition to allowing you to use Laravel's built-in redirector, Livewire supports Laravel's [flash data utilities](https://laravel.com/docs/10.x/session#flash-data).

To pass flash data along with a redirect, you can use Laravels' `->with()` method like so:

```php
use Livewire\Component;

class UpdatePost extends Component
{
    // ...

    public function update()
    {
        // ...

		return redirect('/posts')->with('status', 'Post updated!');
    }
}
```

Assuming the page being redirected to contains the following Blade snippet, the user will see a "Post updated!" message after updating the post.

```html
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
```

### Flashing without redirecting

You may want to flash data to users from the same component they're interacting with without redirecting them away.

You can use Laravel's session data utilities like the following directly from any Livewire action:

```php
public function update()
{
    // ...

    session()->flash('status', 'Post updated!');
}
```
