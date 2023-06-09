<?php

namespace LegacyTests\Browser\Events;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    protected $listeners = ['foo'];

    public $lastEvent = '';

    public function foo($value)
    {
        $this->lastEvent = $value;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
