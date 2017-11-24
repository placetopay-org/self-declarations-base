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
     * @param bool $newEntity
     * @return $this|static
     */
    static public function load(array $attributes = [], $newEntity = true)
    {
        static $entity;
        $entity = ($newEntity) ? new static() : $entity ?: new static();
        $entity = new static();
        $fields = array_merge($entity->fields(), ['dummy']);
        $appends = $entity->appends();

        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $fields)) {
                $setter = setter($attribute);
                method_exists($entity, $setter) ? $entity->{$setter}($value) : $entity->{$attribute} = $value;
            }
        }

        foreach ($appends as $append) {
            $getter = getter($append);
            if (method_exists($entity, $getter)) {
                $entity->{$append} = $entity->{$getter}();
            }
        }

        return $entity;
    }

    /**
     * @param array $dataSets
     * @return array
     */
    static public function loadMultiple(array $dataSets)
    {
        $loadMultiple = [];

        foreach ($dataSets as $dataSet) {
            $loadMultiple[] = static::load($dataSet);
        }

        return $loadMultiple;
    }

    /**
     * @param array $newAttributes
     * @return static
     */
    public function merge(array $newAttributes)
    {
        return static::load($newAttributes, false);
    }
}
