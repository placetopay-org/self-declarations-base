<?php

namespace FreddieGar\Base\Contracts\Commons;

use FreddieGar\Base\Constants\BlameColumn;
use FreddieGar\Base\Contracts\Interfaces\BlameColumnInterface;
use FreddieGar\Base\Traits\BlameColumnsTrait;
use FreddieGar\Base\Traits\LoaderTrait;
use FreddieGar\Base\Traits\ToArrayTrait;

/**
 * Class Entity
 * @package FreddieGar\Base\Contracts\Commons
 */
abstract class EntityJsonApi implements BlameColumnInterface
{
    use BlameColumnsTrait;
    use LoaderTrait;
    use ToArrayTrait;

    /**
     * Properties load in entity
     * @return array
     */
    abstract protected function fields();

    /**
     * This fields are exclude from toArray method
     * return array
     */
    protected function hiddens()
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
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{$name} = null;
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
