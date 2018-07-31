<?php

namespace FreddieGar\Base\Traits;

use App\Entities\TranslationEntity;
use FreddieGar\Base\Constants\CacheKey;
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

        $this->flushDictionaryId($entity);
        $this->cacheFlushRelated($entity);
        $this->flushSelectList();
    }

    /**
     * @param EntityLaravel $entity
     * @return void
     */
    public function updated($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);

        $this->flushDictionaryId($entity);
        $this->cacheFlushRelated($entity);
        $this->flushSelectList();
    }

    /**
     * @param EntityLaravel $entity
     * @return void
     */
    public function deleted($entity)
    {
        self::cacheFlush($entity->id());
        self::cacheFlush(CacheKey::ALL);

        $this->flushDictionaryId($entity);
        $this->cacheFlushRelated($entity);
        $this->flushSelectList();
    }

    /**
     * @param EntityLaravel $entity
     * @return void
     */
    protected function flushDictionaryId($entity)
    {
        if ($entity->dictionaryId()) {
            self::cacheFlush($this->key($entity->dictionaryId(), $this->lang()), TranslationEntity::class);
        }
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
