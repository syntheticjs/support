<?php

namespace Livewire;

use Livewire\Features\SupportAttributes\HandlesAttributes;
use Livewire\Mechanisms\NestingComponents\HandlesNestingComponents;
use Livewire\Mechanisms\DataStore;
use Livewire\Features\SupportValidation\HandlesValidation;
use Livewire\Features\SupportStreaming\HandlesStreaming;
use Livewire\Features\SupportRedirects\HandlesRedirects;
use Livewire\Features\SupportQueryString\HandlesQueryString;
use Livewire\Features\SupportPagination\HandlesPagination;
use Livewire\Features\SupportPageComponents\HandlesPageComponents;
use Livewire\Features\SupportEvents\HandlesEvents;
use Livewire\Features\SupportDisablingBackButtonCache\HandlesDisablingBackButtonCache;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Drawer\Utils;
use Livewire\Drawer\Utils as SyntheticUtils;
use Livewire\Concerns\InteractsWithProperties;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Str;
use BadMethodCallException;

/**
 * @todo - add Facade-esque method signatures to this file (from triggered __get and __call)
 */

abstract class Component
{
    use Macroable { __call as macroCall; }

    use InteractsWithProperties;
    use HandlesEvents;
    use HandlesRedirects;
    use HandlesStreaming;
    use HandlesPagination;
    use HandlesAttributes;
    use HandlesValidation;
    use HandlesPageComponents;
    use HandlesDisablingBackButtonCache;

    protected $__id;
    protected $__name;

    function id()
    {
        return $this->getId();
    }

    function setId($id)
    {
        $this->__id = $id;
    }

    function getId()
    {
        return $this->__id;
    }

    function setName($name)
    {
        $this->__name = $name;
    }

    function getName()
    {
        return $this->__name;
    }

    function skipRender($html = null)
    {
        store($this)->set('skipRender', $html ?: true);
    }

    function skipMount()
    {
        store($this)->set('skipMount', true);
    }

    function skipHydrate()
    {
        store($this)->set('skipHydrate', true);
    }

    function __isset($property)
    {
        try {
            $value = $this->__get($property);

            if (isset($value)) {
                return true;
            }
        } catch(PropertyNotFoundException $ex) {}

        return false;
    }

    protected $lazyProperties = [];

    function makeLazy($property, $callback)
    {
        unset($this->$property);

        $this->lazyProperties[$property] = $callback;
    }

    function __get($property)
    {
        if (isset($this->lazyProperties[$property])) {
            $this->$property = dd($this->lazyProperties[$property]());

            unset($this->lazyProperties[$property]);
        }

        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = trigger('__get', $this, $property, $returnValue);

        $value = $finish($value);

        if ($value === 'noneset') {
            throw new PropertyNotFoundException($property, $this->getName());
        }

        return $value;
    }

    function __call($method, $params)
    {
        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = trigger('__call', $this, $method, $params, $returnValue);

        $value = $finish($value);

        if ($value !== 'noneset') {
            return $value;
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
