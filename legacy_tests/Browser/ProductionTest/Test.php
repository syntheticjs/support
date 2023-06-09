<?php

namespace LegacyTests\Browser\ProductionTest;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function ensure_livewire_runs_when_app_debug_is_set_to_false()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Just need to check input is filled to ensure Livewire has started properly.
                 * Have set app.debug to false inside mount method in component
                 */
                ->assertInputValue('@foo', 'squishy')
                ;
        });
    }
}
