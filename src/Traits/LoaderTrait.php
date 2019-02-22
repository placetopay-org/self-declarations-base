<?php

namespace FreddieGar\Base\Traits;

use Illuminate\Support\Collection;

/**
 * Trait LoaderTrait
 * @package FreddieGar\Base\Traits
 */
trait LoaderTrait
{
    /**
     * @param array $data
     * @return static
     */
    public static function load(array $data = [])
    {
        return isset($data[0])
            ? static::loadMultiple($data)
            : static::loadOne($data);

    }

    /**
     * @param array $attributes
     * @return static
     */
    protected static function loadOne(array $attributes = [])
    {
        $entity = new static();

        foreach ($attributes as $attribute => $value) {
            $entity->{$attribute} = $value;
        }

        return $entity;
    }

    /**
     * @param array $data
     * @return Collection
     */
    protected static function loadMultiple(array $data): Collection
    {
        $loadMultiple = [];

        foreach ($data as $dataSet) {
            $loadMultiple[] = static::load($dataSet);
        }

        return collect($loadMultiple);
    }
}
