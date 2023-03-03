<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Sushi\Sushi;
use LegacyTests\Browser\TestCase;

class DataBindingModelsTest extends TestCase
{
    use WithLegacyModels;
    
    /** @test */
    public function it_displays_all_nested_data()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertValue('@author.name', 'Bob')
                ->assertValue('@author.email', 'bob@bob.com')
                ->assertValue('@author.posts.0.title', 'Post 1')
                ->assertValue('@author.posts.0.comments.0.comment', 'Comment 1')
                ->assertValue('@author.posts.0.comments.0.author.name', 'Bob')
                ->assertValue('@author.posts.0.comments.1.comment', 'Comment 2')
                ->assertValue('@author.posts.0.comments.1.author.name', 'John')
                ->assertValue('@author.posts.1.title', 'Post 2')
                ;
        });
    }

    /** @test */
    public function it_enables_nested_data_to_be_changed()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->waitForLivewire()->type('@author.name', 'Steve')
                ->assertSeeIn('@output.author.name', 'Steve')

                ->waitForLivewire()->type('@author.posts.0.title', 'Article 1')
                ->assertSeeIn('@output.author.posts.0.title', 'Article 1')

                ->waitForLivewire()->type('@author.posts.0.comments.0.comment', 'Message 1')
                ->assertSeeIn('@output.author.posts.0.comments.0.comment', 'Message 1')

                ->waitForLivewire()->type('@author.posts.0.comments.1.author.name', 'Mike')
                ->assertSeeIn('@output.author.posts.0.comments.1.author.name', 'Mike')

                ->waitForLivewire()->click('@save')
                ;
        });

        $author = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $this->assertEquals('Steve', $author->name);
        $this->assertEquals('Article 1', $author->posts[0]->title);
        $this->assertEquals('Message 1', $author->posts[0]->comments[0]->comment);
        $this->assertEquals('Mike', $author->posts[0]->comments[1]->author->name);

        // Reset back after finished.
        $author->name = 'Bob';
        $author->posts[0]->title = 'Post 1';
        $author->posts[0]->comments[0]->comment = 'Comment 1';
        $author->posts[0]->comments[1]->author->name = 'John';
        $author->push();
    }
}

class Component extends BaseComponent
{
    public $author;

    protected $rules = [
        'author.name' => '',
        'author.email' => '',
        'author.posts.*.title' => '',
        'author.posts.*.comments.*.comment' => '',
        'author.posts.*.comments.*.author.name' => '',
    ];

    public function mount()
    {
        $this->author = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();
    }

    public function save()
    {
        $this->author->push();
    }

    public function render()
    {
        return
        <<<'HTML'
<div>
    <div>
        Author Name
        <input dusk='author.name' wire:model.live='author.name' />
        <span dusk='output.author.name'>{{ $author->name }}</span>

        Author Email
        <input dusk='author.email' wire:model.live='author.email' />
        <span dusk='output.author.email'>{{ $author->email }}</span>
    </div>

    <div>
        @foreach($author->posts as $postKey => $post)
            <div>
                Post Title
                <input dusk='author.posts.{{ $postKey }}.title' wire:model.live="author.posts.{{ $postKey }}.title" />
                <span dusk='output.author.posts.{{ $postKey }}.title'>{{ $post->title }}</span>

                <div>
                    @foreach($post->comments as $commentKey => $comment)
                        <div>
                            Comment Comment
                            <input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'
                                wire:model.live="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                />
                            <span dusk='output.author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'>{{ $comment->comment }}</span>

                            Commment Author Name
                            <input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'
                                wire:model.live="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                />
                            <span dusk='output.author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'>{{ optional($comment->author)->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <button wire:click="save" type="button" dusk="save">Save</button>
</div>
HTML;
    }
}

class Author extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'Bob', 'email' => 'bob@bob.com'],
        ['id' => 2, 'name' => 'John', 'email' => 'john@john.com']
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1],
        ['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1],
        ['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}