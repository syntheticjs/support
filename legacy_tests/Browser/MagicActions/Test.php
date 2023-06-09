<?php

namespace LegacyTests\Browser\MagicActions;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;
use LegacyTests\Browser\MagicActions\Component;

class Test extends TestCase
{
    public function test_magic_toggle_can_toggle_properties()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                //Toggle boolean property
                ->assertSeeIn('@output', 'false')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output', 'true')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output', 'false')

                //Toggle nested boolean property
                ->assertSeeIn('@outputNested', 'false')
                ->waitForLivewire()->click('@toggleNested')
                ->assertSeeIn('@outputNested', 'true')
                ->waitForLivewire()->click('@toggleNested')
                ->assertSeeIn('@outputNested', 'false')
            ;
        });
    }

    public function test_magic_event_works()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->assertDontSeeIn('@outputEvent', 'baz')
                ->waitForLivewire()->click('@fillBar')
                ->assertSeeIn('@outputEvent', 'baz')
            ;
        });
    }
}
