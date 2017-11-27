<?php

namespace FreddieGar\Base\Traits;

use FreddieGar\Base\Constants\CacheKey;
use App\Entities\TranslationEntity;
use FreddieGar\Base\Contracts\Commons\EntityLaravel;

/**
 * Trait ManagerEventTrait
 * @method string lang() DictionaryTrait
 * @package FreddieGar\Base\Traits
 */
trait ManagerEventTrait
{
    /**
     * @param EntityLaravel $entity
     * @return void
     */
    public function created($entity)
    {
        self::cacheFlush(CacheKey::ALL);
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
        $this->flushSelectList();
        $this->cacheFlushRelated($entity);
    }

    /**
     * @param EntityLaravel $entity
     * @return void
     */
    public function updated($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
        $this->flushSelectList();
        $this->cacheFlushRelated($entity);
    }

    /**
     * @param EntityLaravel $entity
     * @return void
     */
    public function deleted($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
        $this->flushSelectList();
        $this->cacheFlushRelated($entity);
    }

    /**
     * @return void
     */
    protected function flushSelectList()
    {
        if (method_exists($this, 'getSelectList')) {
            self::cacheFlushSelectList();
        }
    }

    /**
     * Please implements this method in your manager
     * @param EntityLaravel $entity
     * @return void
     */
    protected function cacheFlushRelated($entity)
    {
    }
}
