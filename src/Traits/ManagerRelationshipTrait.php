<?php

namespace FreddieGar\Base\Traits;

use App\Entities\UserEntity;
use App\Managers\UserManager;

/**
 * Trait ManagerRelationshipTrait
 * @package FreddieGar\Base\Traits
 */
trait ManagerRelationshipTrait
{
    /**
     * @inheritdoc
     */
    public function createdBy($model_id)
    {
        $tag = makeTagNameCache([__METHOD__, $model_id]);

        $createdBy = static::model()->getFromCacheTag($tag, function () use ($model_id) {
            return static::repository()->createdBy($model_id);
        });

        return UserManager::response(UserEntity::load($createdBy));
    }

    /**
     * @inheritdoc
     */
    public function updatedBy($model_id)
    {
        $tag = makeTagNameCache([__METHOD__, $model_id]);

        $updatedBy = static::model()->getFromCacheTag($tag, function () use ($model_id) {
            return static::repository()->updatedBy($model_id);
        });

        return UserManager::response(UserEntity::load($updatedBy));
    }
}
