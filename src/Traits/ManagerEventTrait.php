<?php

namespace FreddieGar\Base\Traits;

use App\Constants\CacheKey;
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
     * @return bool
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
     * @return bool
     */
    public function updated($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);
        self::cacheFlush($this->key(CacheKey::HAVE_INFORMATION_RELATED, $entity->id()));
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
        $this->flushSelectList();
        $this->cacheFlushRelated($entity);
    }

    /**
     * @param EntityLaravel $entity
     * @return bool
     */
    public function deleted($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);
        self::cacheFlush($this->key(CacheKey::HAVE_INFORMATION_RELATED, $entity->id()));
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
        $this->flushSelectList();
        $this->cacheFlushRelated($entity);
    }

    protected function flushSelectList()
    {
        if (method_exists($this, 'getSelectList')) {
            self::cacheFlushSelectList();
        }
    }

    /**
     * Please implements this method in your manager
     * @param EntityLaravel $entity
     */
    protected function cacheFlushRelated($entity)
    {
        ff('Don\'t have cache related: ' . get_class($entity));
    }
}
