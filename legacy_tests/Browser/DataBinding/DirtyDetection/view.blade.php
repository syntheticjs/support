<div>
    <input wire:model.live="foo" dusk="foo.input">
    <button wire:click="changeFoo" dusk="foo.button">Change Foo</button>

    <input wire:model.live="bar.baz" dusk="bar.input">
    <button wire:click="resetBar" dusk="bar.button">Change BarBaz</button>
</div>
