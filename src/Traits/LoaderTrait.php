<?php

namespace FreddieGar\Base\Traits;

/**
 * Trait LoaderTrait
 * @package FreddieGar\Base\Traits
 */
trait LoaderTrait
{
    /**
     * @param array $attributes
     * @return $this|static
     */
    public static function load(array $attributes = [])
    {
        $entity = new static();

        foreach ($attributes as $attribute => $value) {
            $entity->{$attribute} = $value;
        }

        return $entity;
    }

    /**
     * @param array $dataSets
     * @return array
     */
    public static function loadMultiple(array $dataSets)
    {
        $loadMultiple = [];

        foreach ($dataSets as $dataSet) {
            $loadMultiple[] = static::load($dataSet);
        }

        return $loadMultiple;
    }
}
