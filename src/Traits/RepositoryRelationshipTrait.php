<?php

namespace FreddieGar\Base\Traits;

/**
 * Trait RepositoryRelationshipTrait
 * @package FreddieGar\Base\Traits
 */
trait RepositoryRelationshipTrait
{
    /**
     * @inheritdoc
     */
    static public function createdBy($entity_id)
    {
        if ($createdBy = static::model()->findOrFail($entity_id)->createdBy) {
            return $createdBy->toArray();
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    static public function updatedBy($entity_id)
    {
        if ($updatedBy = static::model()->findOrFail($entity_id)->updatedBy) {
            return $updatedBy->toArray();
        }

        return [];
    }
}
