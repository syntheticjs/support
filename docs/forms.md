Because forms are the backbone of most web applications, Livewire provides loads of helpful utilities for building them. From handling simple input elements to complex things like real-time validation or file uploading, Livewire has simple, well-documented, tools to make your life easire and delight your users.

Let's dive in.

## Submitting a form

Let's start by looking at the most basic form in a `CreatePost` component. This form will have two simple text inputs and a submit button, as well as some code in the backend to manage the state and submission.

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
        Post::create(
            $this->only('title', 'content')
        );

        return $this->redirect(
            ShowPosts::class
        )->with('status', 'Post successfully created.');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<form wire:submit="save">
    <input type="text" wire:model="title">

    <input type="text" wire:model="content">

    <button type="submit">Save</button>
</form>
```

As you can see, we are "binding" the public `$title` and `$content` properties in the above form using `wire:model`. This is one of the most commonly used and powerful features of Livewire.

In addition to binding `title` and `content`, we are using `wire:submit` to capture the `submit` event when the "Save" button is pressed and persist the form input to the database.

After the new post is created in the database, we redirect the user to the `ShowPosts` component page and show them a flash message that the new post was created.

### Adding validation

To avoid storing incomplete or dangerous user input, most forms need some sort of input validation.

Livewire makes validating your forms as simple as adding `#[Rule]` attributes above the properties you want to be validated.

Once a property has a `#[Rule]` attribute attached to it, any time it's updated server-side, it will be processed through that validation rule first.

Let's add some basic validation rules to the `$title` and `$content` properties in our `CreatePosts` component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    #[Rule('required')] // [tl! highlight]
    public $title = '';

    #[Rule('required')] // [tl! highlight]
    public $content = '';

    public function save()
    {
        Post::create(
            $this->only('title', 'content')
        );

        return $this->redirect(
            ShowPosts::class
        );
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

We'll also modify our Blade template to show any validation errors on the page.

```html
<form wire:submit="save">
    <input type="text" wire:model="title">
    @error('title') <span class="error">{{ $message }}</span> @enderror <!-- [tl! highlight] -->

    <input type="text" wire:model="content">
    @error('content') <span class="error">{{ $message }}</span> @enderror <!-- [tl! highlight] -->

    <button type="submit">Save</button>
</form>
```

Now, if the user tries to submit the form without filling in any of the fields, they will see validation messages telling them which fields are required before saving the post.

There's a lot more you can do with validation in Livewire. For more information, visit the [dedicated documentation page on Validation](/docs/validation).

### Extracting a form object

If you are working with a large form and prefer to extract all of its properties, validation logic, etc., into a separate class, Livewire offers form objects.

Form objects are a simple abstraction that allows you to re-use form logic across components as well as just provide a nice way to keep your component class cleaner and group all form-related code into a separate class.

Here's the above `CreatePost` component rewritten to use a `PostForm` class:

```php
<?php

namespace App\Forms;

use Livewire\Form;

class PostForm extends Form
{
    #[Rule('required|min:5')]
    public $title = '';

    #[Rule('required|min:5')]
    public $content = '';
}
```

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Forms\PostForm;

class CreatePost extends Component
{
    public PostForm $form;

    public function save()
    {
        Post::create(
            $this->form->all()
        );

        return $this->redirect(
            ShowPosts::class
        );
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<form wire:submit="save">
    <input type="text" wire:model="form.title">
    @error('form.title') <span class="error">{{ $message }}</span> @enderror

    <input type="text" wire:model="form.content">
    @error('form.content') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save</button>
</form>
```

If you'd like, you can also extract the post creation logic into the form object like so:

```php
<?php

namespace App\Forms;

use Livewire\Form;
use App\Models\Post;

class PostForm extends Form
{
    #[Rule('required|min:5')]
    public $title = '';

    #[Rule('required|min:5')]
    public $content = '';

    public function store()
    {
        Post::create($this->all());
    }
}
```

Now you can call `$this->form->store()` from the component directly:

```php
class CreatePost extends Component
{
    public PostForm $form;

    public function save()
    {
        $this->form->store();

        return $this->redirect(
            ShowPosts::class
        );
    }

    // ...
}
```

If you want to use this form object for both a create and update form, you can easily adapt it.

Here's what it would look like to use this same form object for an `UpdatePost` component and fill it with initial data:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Forms\PostForm;
use App\Models\Post;

class UpdatePost extends Component
{
    public PostForm $form;

    public function mount(Post $post)
    {
        $this->form->setPost($post);
    }

    public function save()
    {
        $this->form->update();

        return $this->redirect(
            ShowPosts::class
        );
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```php
<?php

namespace App\Forms;

use Livewire\Form;
use App\Models\Post;

class PostForm extends Form
{
    public Post $post;

    #[Rule('required|min:5')]
    public $title = '';

    #[Rule('required|min:5')]
    public $content = '';

    public function setPost(Post $post)
    {
        $this->post = $post;

        $this->title = $post->title;

        $this->content = $post->content;
    }

    public function store()
    {
        Post::create($this->all());
    }

    public function update()
    {
        $this->post->update(
            $this->all()
        );
    }
}
```

As you can see, we've added a `setPost` method to the `PostForm` object to optionally allow for filling the form with existing data as well as storing the post on the object for later use. We've also added an `update()` method for updating the existing post.

Form objects are not required in any way for working with Livewire, but they do offer a nice abstraction for keeping your components free of repetitive boilerplate.

### Showing a loading indicator

By default, Livewire will automatically disable submit buttons and mark inputs as `readonly` while a form is being submitted. This makes it so that a user can't submit a form again while the first submission is being handled.

However, it can be difficult to detect this "loading" state for users without extra affordances in the UI.

Here's an example of adding a small loading spinner to the "Save" button via `wire:loading` so that a user understands that the form is being submitted server-side:

```html
<button type="submit">
    Save

    <div wire:loading>
        <svg>...</svg> <!-- SVG loading spinner -->
    </div>
</button>
```

Now, when a user presses "Save", a small, inline spinner will show up.

Livewire's `wire:loading` feature has a lot more to it. Visit the [dedicated documentation page for more info.](/docs/loading)

## Live-updating fields

By default, Livewire only sends a network request when the form is submitted (or any other [action](/docs/actions) is called), not while the form is being filled out.

Take the `CreatePost` component, for example. If you wanted to make sure the "title" input field was being synchronized with the `$title` property on the backend AS the user typed, you could add the `.live` modifier to `wire:model` like so:

```html
<input type="text" wire:model.live="title">
```

Now, as a user types into this field, network requests will be sent to the server to update `$title`. This is useful for things like a real-time search, where a dataset is filtered as a user types into a search box.

## Only updating fields on _blur_

For most cases, `wire:model.live` is fine for real-time form field updating; however, it can be a overly network resource-intensive on text inputs.

If instead of sending network requests as a user types, you want to instead only send the request when a user "tabs" out of the text input (also referred to as "blurring" an input), you can use the `.blur` modifier instead.

```html
<input type="text" wire:model.blur="title" >
```

Now the component class on the server won't be updated until the user presses tab or clicks away from the text input.

## Real-time validation

Sometimes, you may want to show validation errors as the user fills out the form. This way, they are alerted early that something is wrong instead of having to wait until the entire form is filled out.

Livewire handles this sort of thing automatically. By using `.live` or `.blur` on `wire:model`, Livewire will send network requests as the user fills out the form. Each of those network requests will run the appropriate validation before updating each property. If validation fails, the property won't be updated on the server, and a validation message will be shown to the user:

```html
<input type="text" wire:model.blur="title">

@error('title') <span class="error">{{ $message }}</span> @enderror
```

```php
#[Rule('required|min:5')]
public $title = '';
```

Now, if the user only types three characters into the "title" input, then clicks on the next input in the form, a validation message will be shown to them indicating there is a five character minimum for that field.

For more information, check out the [validation documentation page](/docs/validation).

## Real-time form saving

If you want to automatically save a form as the user fills it out rather than wait till end when they press "submit", you can do so using Livewire's `update()` hook:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
    public Post $post;

    #[Rule('required')]
    public $title = '';

    #[Rule('required')]
    public $content = '';

    public function mount(Post $post)
    {
        $this->post = $post;
    }

    public function updated($name, $value)
    {
        $this->post->update([
            $name => $value,
        ]);
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<form wire:submit.prevent>
    <input type="text" wire:model.blur="title">
    @error('title') <span class="error">{{ $message }}</span> @enderror

    <input type="text" wire:model.blur="content">
    @error('content') <span class="error">{{ $message }}</span> @enderror
</form>
```

In the above example, when a user completes a field (by clicking on or tabbing onto the next one), a network request is sent to update that property on the component. Immediately after the property is updated on the class, the `updated` hook is called for that specific property name and its new value.

This way, we can use that hook to update only that specific field in the database.

Additionally, because we have the `#[Rule]` attributes attached to those properties, the validation rules will be run before the property is updated and the `updated()` hook is called.

To learn more about the "updated" lifecycle hook and other hooks [visit the lifecycle hooks documentation](/docs/lifecycle-hooks).

## Showing dirty indicators

In the above real-time saving scenario, it may be helpful to indicate to users when a field hasn't been persisted to the database yet.

For example, if a user visits an `UpdatePost` page and starts modifying the title of the post in a text input, it may be unclear to them when the title is actually being updated in the database. Especially if there is no "Save" button at the bottom of the form.

Livewire provides a convenient utility called `wire:dirty` that allows you to toggle elements or modify classes when an input's value diverges from the server-side component.

Take a look at the following example:

```html
<input type="text" wire:model.blur="title" wire:dirty.class="border-yellow">
```

In the above code, when a user types into the input field, a yellow border around it will appear. When they tab away, the network request is sent and the border will disappear; signaling to them that the input has been persisted and is no longer "dirty".

If you want to toggle an entire element's visibility, you can do so by using `wire:dirty` in conjunction with `wire:target`. `wire:target` is necessary to specify which piece of data you want to watch for "dirtiness". In this case, the "title" field:

```html
<input type="text" wire:model="title">

<div wire:dirty wire:target="title">Unsaved...</div>
```

## Debouncing input

When using `.live` on a text input, you may want more fine-grained control over how often a network request is sent. By default, a debounce of "250ms" is applied to the input; however, you can customize this using the `.debounce` modifier like so:

```html
<input type="text" wire:model.live.debounce.150ms="title" >
```

Now that `.debounce.150ms` has been added, a shorter debounce of "150ms" will be used when handling input updates for this field. In other words, as a user types, a network request will only be sent if the user stops typing for at least 150 milliseconds instead of after 250ms by default.

## Throttling input

As stated before, when an input debounce is applied to a field, it will hold off on triggering a network request until after the user has stopped typing for a certain amount of time. This means if the user continues typing a long message, a network request won't be sent until the user is finished.

Sometimes this isn't the desired behavior, and you'd rather send a request AS the user types, not when they've finished or taken a break.

In these cases, you can instead use `.throttle` to signify a time interval to send network requests:

```html
<input type="text" wire:model.live.throttle.150ms="title" >
```

In the above example, as a user is typing continuously in the "title" field, a network request will be sent every 150 milliseconds until the user is finished. 

## Extracting input fields to Blade components

Even in a small component such as the `CreatePost` example we've been using, we end up duplicating lots of form field code like validation messages and labels.

It can be helpful to extract repetitive UI elements such as these into dedicated [Blade components](https://laravel.com/docs/10.x/blade#components) to be shared across your application.

For example, here is the original Blade template from the `CreatePost` component. We will be extractin the following two text inputs into dedicated Blade components:

```html
<form wire:submit="save">
    <input type="text" wire:model="title"> <!-- [tl! highlight] -->
    @error('title') <span class="error">{{ $message }}</span> @enderror <!-- [tl! highlight] -->

    <input type="text" wire:model="content"> <!-- [tl! highlight] -->
    @error('content') <span class="error">{{ $message }}</span> @enderror <!-- [tl! highlight] -->

    <button type="submit">Save</button>
</form>
```

Here's what the template will look like after extract a re-usable Blade component called `<x-input-text>`:

```html
<form wire:submit="save">
    <x-input-text name="title" wire:model="title" /> <!-- [tl! highlight] -->

    <x-input-text name="content" wire:model="content" /> <!-- [tl! highlight] -->

    <button type="submit">Save</button>
</form>
```

Now here's the source for the `x-input-text` component:

```html
<!-- resuorces/views/components/input-text.blade.php -->

@props(['name'])

<input type="text" name="{{ $name }}" {{ $attributes }}>

@error($name) <span class="error">{{ $message }}</span> @enderror
```

As you can see, we took the repetitive HTML and placed it inside a dedicated Blade component.

For the most part, the Blade component contains the extracted HTML, except two added bits:

* The `@props` directive at the top
* The `{{ $attributes }}` statement on the input.

Let's walk through each:

By specifying `name` as a "prop" using `@props(['name'])` we are telling Blade: Hey, if an attribute called "name" is set on this component, take its value and make it available inside this component as `$name`.

This way, we can re-use the passed-in `name` value around our template like the `<input>` "name" attribute and the `@error($name)` directive in this case.

For other attributes that don't have an explicit purpose, we used the `{{ $attributes }}` statement. This is used for "attribute forwarding", or in other words, taking any HTML attributes written on the Blade component and forwarding them onto an element within the component, `<input>` in our case.

This ensures `wire:model="title"` and any other extra attributes such as `disabled`, `class="..."` or `required`, still get forwarded to the actual `<input>` element.

### Custom form controls 

In the previous example, we "wrapped" an input element into a nice, re-usable Blade component we can use as if it was a native HTML input element.

This pattern will get you pretty far; however, there might be some cases where you want to create an entire input component from scratch (without an underlying native input element), but still be able to bind it's value to Livewire properties using `wire:model`.

For example, let's say you wanted to create an `<x-input-counter />` component that was a simple "counter" input written in Alpine.

Before we create a Blade component, let's first look at a simple, pure-Alpine, "counter" component for reference:

```html
<div x-data="{ count: 0 }">
    <button x-on:click="count--">-</button> 

    <span x-text="count"></span>

    <button x-on:click="count++">+</button> 
</div>
```

As you can see, the above component shows a number alongside two buttons to increment and decrement that number.

Now let's say we want to wrap this component up into a Blade component called `<x-input-counter />` that we would use within a component like so:

```html
<x-input-counter wire:model="quantity" />
```

For the most part, creating this component is simple. We take the HTML of the counter and place it inside a Blade component template like `resources/views/components/counter.blade.php`.

However, making it work with `wire:model="quantity"` so that you can easily bind data from your Livewire component to the "count" inside this Alpine component needs one extra step.

Here's the source for the component:

```html
<!-- resources/view/components/input-counter.blade.php -->

<div x-data="{ count: 0 }" x-modelable="count" {{ $attributes}}>
    <button x-on:click="count--">-</button> 

    <span x-text="count"></span>

    <button x-on:click="count++">+</button> 
</div>
```

As you can see, the only different bit about this HTML is the `x-modelable="count"` and `{{ $attributes }}`.

`x-modelable` is a utility in Alpine that tells Alpine to make a certain piece of data available for binding from outside. [You can read the Alpine documentation page on it for more info.](https://alpinejs.dev/directives/modelable)

`{{ $attributes }}`, as we explored earlier, forwards any attributes passed into the Blade component from outside, `wire:model` in our case.

Because of `{{ $attributes }}`, when the HTML is rendered in the browser, `wire:model="quantity"` will be rendered alongside `x-modelable="count"` directly on the root `<div>` of the Alpine component like so:

```html
<div x-data="{ count: 0 }" x-modelable="count" wire:model="quantity">
```

`x-modelable="count"` tells Alpine to look for any `x-model` or `wire:model` statements and use "count" as the data to bind them to.

Because `x-modelable` works for both `wire:model` and `x-model`, you can also use this Blade component interchangeably with Livewire and Alpine. For example, here's an example of using this Blade component in a purely Alpine context:

```html
<x-input-counter x-model="quantity" />
```

Creating custom input elements in your application is extremely powerful but requires a deeper understanding of the utilities Livewire and Alpine provide and how they interact with each other.

## Input fields

Livewire supports most native input elements out of the box. Meaning you should just be able to attach `wire:model` to any input element in the browser and easily bind properties to them.

Here's a comprehensive list of the different available input types and how you use them in a Livewire context.

### Text inputs

First and foremost, text inputs are the bedrock of most forms. Here's how to bind a property called "title" to one:

```html
<input type="text" wire:model="title">
```

### Textarea inputs

Textarea elements are similarly straightforward. Simply add `wire:model` to one, and the values will be bound.

```html
<textarea type="text" wire:model="content"></textarea>
```

If the "content" value in this case, is filled with a string initially, Livewire will handle filling the textarea with that value; there's no need to do something like this:

```html
<!-- Warning: this snippet demonstrates what NOT to do... -->

<textarea type="text" wire:model="content">{{ $content }}</textarea>
```

### Checkboxes

Checkboxes can be used for single values, for example, when toggling a boolean property, or for toggling a single value in a group of related values. We'll cover both scenarios:

#### Single checkbox

At the end of a signup form, you might have a checkbox allowing the user to opt-in to email updates. You might call this property `$receiveUpdates` internally. You can easily bind this value to the checkbox using `wire:model` like so:

```html
<input type="checkbox" wire:model="receiveUpdates">
```

Now when the `receiveUpdates` value is false, the checkbox will be unchecked, and when it's true, it will be checked.

#### Multiple checkboxes

Now, let's say in addition to deciding to receive updates, you have an array property in your class called `$updateTypes` like so:

```php
public $updateTypes = [];
```

You can allow the user to choose any of the following update types, and they will be added to the `$updateTypes` property on the component:

```html
<input type="checkbox" value="email" wire:model="updateTypes">
<input type="checkbox" value="sms" wire:model="updateTypes">
<input type="checkbox" value="notificaiton" wire:model="updateTypes">
```

For example, if the user checks the first two boxes but not the third, the value of `$updateTypes` will be: `["email", "sms"]`

### Radio buttons

To toggle between two different values for a single property, you can use radio buttons like so:

```html
<input type="radio" value="yes" wire:model="receiveUpdates">
<input type="radio" value="no" wire:model="receiveUpdates">
```

### Select dropdowns

Livewire makes working with `<select>` dropdowns simple. By adding `wire:model` to one, the currently selected value will be bound to the provided property name and vice versa.

In addition, there's no need to manually add `selected` to the option that will be selected; Livewire handles that for you automatically.

Here's an example of a select box filled with a static list of states.

```html
<select wire:model="state">
    <option value="AL">Alabama<option>
    <option value="AK">Alaska</option>
    <option value="AZ">Arizona</option>
    ...
</select>
```

When a specific state is selected, for example, "Alaska", the `$state` property on the component will be set to `AK`. If you'd prefer the value to be set to "Alaska" instead of "AK", you can leave the `value=""` attribute off the `<option>` element entirely.

Here's an example using a dynamic list of options generated by Blade:

```html
<select wire:model="state">
    @foreach (\App\Models\State::all() as $state)
        <option value="{{ $option->id }}">{{ $option->label }}</option>
    @endforeach
</select>
```

If you don't have a specific option selected by default, you may want to show a muted placeholder option by default, such as "Select a state":

```html
<select wire:model="state">
    <option disabled>Select a state...</option>

    @foreach (\App\Models\State::all() as $state)
        <option value="{{ $option->id }}">{{ $option->label }}</option>
    @endforeach
</select>
```

As you can see, there is no "placeholder" attribute for a select menu like there is for text inputs. Instead, you have to add a `disabled` option element as the first option in the list.

### Multi-select dropdowns

If you are using a "multiple" select menu, Livewire works as expected. By specifying the `$states` property in the below `wire:model`, all selected options will be toggled inside the array value rather than a single value.

```html
<select wire:model="states" multiple>
    <option value="AL">Alabama<option>
    <option value="AK">Alaska</option>
    <option value="AZ">Arizona</option>
    ...
</select>
```

