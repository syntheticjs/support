<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Component;
use Livewire\DuskTestCase;

class Test extends DuskTestCase
{
    /** @test */
    function can_lock_property()
    {
        $this->visit(new class extends Component {
            /** @locked */
            public $foo = 'bar';

            public function sync() {}

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input wire:model="foo" dusk="input">
                    <button wire:click="sync" dusk="sync">sync</button>

                    <span dusk="output">{{ $foo }}</span>
                </div>
                HTML;
            }
        }, function ($browser) {
            $browser->assertSeeIn('@output', 'bar');
            $browser->type('@input', 'baz');
            $browser->waitForLivewire()->click('@sync');
            $browser->assertSeeIn('@output', 'bar');
        });
    }
}