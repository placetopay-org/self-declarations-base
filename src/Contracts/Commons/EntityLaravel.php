<?php

namespace FreddieGar\Base\Contracts\Commons;

use FreddieGar\Base\Constants\BlameColumn;
use FreddieGar\Base\Contracts\Interfaces\BlameColumnInterface;
use FreddieGar\Base\Traits\BlameColumnsTrait;
use FreddieGar\Base\Traits\DirTyTrait;
use FreddieGar\Base\Traits\LoaderTrait;
use FreddieGar\Base\Traits\ToArrayTrait;

/**
 * Class Entity
 *
 * @method $this|int id($id = null)
 * @method $this|int dictionaryId($dictionaryId = null)
 * @method $this|bool dummy()
 *
 * @package FreddieGar\Base\Contracts\Commons
 */
abstract class EntityLaravel implements BlameColumnInterface
{
    use BlameColumnsTrait;
    use LoaderTrait;
    use ToArrayTrait;
    use DirtyTrait;

    /**
     * Properties load in entity
     * @return array
     */
    abstract protected function fields();

    /**
     * This fields are exclude from toArray method
     * return array
     */
    protected function hidden()
    {
        return [];
    }

    /**
     * This fields are append to entity
     * return array
     */
    protected function appends()
    {
        return [];
    }

    /**
     * This fields are exclude from toArray method
     * return array
     */
    protected function blames()
    {
        return [
            BlameColumn::CREATED_BY,
            BlameColumn::UPDATED_BY,
            BlameColumn::DELETED_BY,
            BlameColumn::CREATED_AT,
            BlameColumn::UPDATED_AT,
            BlameColumn::DELETED_AT,
        ];
    }

    /**
     * @return bool
     */
    public function haveInformationRelated()
    {
        return false;
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
        return isset($this->{$name}) ? $this->{$name} : null;
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
