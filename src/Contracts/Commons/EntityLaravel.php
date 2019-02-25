<?php

namespace FreddieGar\Base\Contracts\Commons;

use FreddieGar\Base\Contracts\Interfaces\BlameColumnInterface;
use FreddieGar\Base\Traits\BlameColumnsTrait;
use FreddieGar\Base\Traits\DirTyTrait;
use Illuminate\Support\Collection;

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
    use DirtyTrait;

    /**
     * Properties available load in entity
     *
     * @return array
     */
    abstract protected function fields(): array;

    /**
     * Get column name primary key
     *
     * @return string
     */
    public static function getKeyName(): string
    {
        return 'id';
    }

    /**
     * Have information related
     *
     * @return bool
     */
    public function haveInformationRelated(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $toArray = [];

        foreach ($this->fields() as $property) {
            if (property_exists($this, $property)) {
                $toArray[$property] = $this->{$property};
            }
        }

        return $toArray;
    }

    /**
     * @param array $data
     * @return static|Collection
     */
    public static function load(array $data)
    {
        return !empty($data) && !isset($data[0])
            ? static::loadOne($data)
            : static::loadMultiple($data);
    }

    /**
     * @param array $attributes
     * @return EntityLaravel
     */
    protected static function loadOne(array $attributes = []): self
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
