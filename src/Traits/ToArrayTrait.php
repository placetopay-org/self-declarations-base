<?php

namespace FreddieGar\Base\Traits;

/**
 * Trait ToArrayTrait
 * @mixin LoaderTrait
 * @package FreddieGar\Base\Traits
 */
trait ToArrayTrait
{
    /**
     * @param bool $includeHidden
     * @return array
     */
    public function toArray($includeHidden = false)
    {
        $toArray = [];
        $properties = $includeHidden ? static::fields() : array_diff(static::fields(), static::hidden());

        foreach ($properties as $property) {
            if (isset($this->{$property})) {
                $toArray[$property] = $this->{$property};
            }
        }

        return $toArray;
    }

    /**
     * @param array $dataSets
     * @param bool $includeHidden
     * @return array
     */
    public static function toArrayMultiple(array $dataSets, $includeHidden = false)
    {
        $toArrayMultiple = [];

        foreach ($dataSets as $dataSet) {
            $toArrayMultiple[] = static::load($dataSet)->toArray($includeHidden);
        }

        return $toArrayMultiple;
    }
}
