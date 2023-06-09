<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void component($alias, $viewClass)
 * @method static \Livewire\Features\SupportUnitTesting\Testable test($name, $params = [])
 * @method static \Livewire\LivewireManager actingAs($user, $driver = null)
 * @method static \Livewire\LivewireManager withQueryParams($queryParams)
 *
 * @see \Livewire\Manager
 */
class Livewire extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'livewire';
    }
}
