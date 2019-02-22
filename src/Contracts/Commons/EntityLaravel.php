<?php

namespace FreddieGar\Base\Contracts\Commons;

use FreddieGar\Base\Contracts\Interfaces\BlameColumnInterface;
use FreddieGar\Base\Traits\BlameColumnsTrait;
use FreddieGar\Base\Traits\DirTyTrait;
use FreddieGar\Base\Traits\LoaderTrait;

/**
 * Class Entity
 *
 * @method $this|int id($id = null)
 * @method $this|int dictionaryId($dictionaryId = null)
 *
 * @package FreddieGar\Base\Contracts\Commons
 */
abstract class EntityLaravel implements BlameColumnInterface
{
    use BlameColumnsTrait;
    use LoaderTrait;
    use DirtyTrait;

    /**
     * Properties load in entity
     * @return array
     */
    abstract protected function fields();

    /**
     * Get column name primary key
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * @return bool
     */
    public function haveInformationRelated()
    {
        return false;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $toArray = [];

        foreach (static::fields() as $property) {
            if (isset($this->{$property})) {
                $toArray[$property] = $this->{$property};
            }
        }

        return $toArray;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|mixed
     */
    public function __call($name, $arguments)
    {
        $property = snake_case($name);

        if (count($arguments) > 0) {
            return self::__set($property, ...$arguments);
        }

        return self::__get($property);
    }

    /**
     * Entity to json string
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray(), 0);
    }
}
