<?php

namespace FreddieGar\Base\Traits;

use Illuminate\Http\Request;

/**
 * Trait RequestTrait
 * @method Request request($request = null);
 * @package FreddieGar\Base\Traits
 */
trait RequestLaravelTrait
{
    /**
     * @param $name
     * @return string
     */
    protected function parseInputName($name)
    {
        return str_replace('.', '_', $name);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    final protected function requestAttribute($name = null, $default = null)
    {
        $attributes = $name
            ? $this->request()->input($this->parseInputName($name), $default)
            : $this->request()->input();

        return $attributes ?: [];
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    final protected function requestFilter($name, $default = null)
    {
        return $this->request()->input($this->parseInputName($name), $default);
    }

    /**
     * @param array $keys
     * @return array
     */
    final protected function requestExcept(array $keys = [])
    {
        return $this->request()->except($keys);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    final protected function requestAddFilter($name, $value = null)
    {
        $this->request()->merge([$this->parseInputName($name) => $value]);
    }
}
