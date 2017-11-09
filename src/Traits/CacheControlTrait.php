<?php

namespace FreddieGar\Base\Traits;

use FreddieGar\Base\Constants\Event;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Trait CacheControlTrait
 * @package FreddieGar\Base\Traits
 */
trait CacheControlTrait
{
    /**
     * @var bool
     */
    static private $ENABLED_CACHE = true;

    /**
     * @var string
     */
    static private $TAG = null;

    /**
     * The "booting" method of the model.
     * @return void
     */
    static protected function bootCacheControlTrait()
    {
        if (static::hasEnableCache()) {
            static::{Event::CREATED}(function (Model $model) {
                /** @noinspection PhpUndefinedFieldInspection */
                static::setCacheById($model->id, $model->toArray());
                static::unsetByTag();
            });

            static::{Event::UPDATED}(function (Model $model) {
                /** @noinspection PhpUndefinedFieldInspection */
                static::setCacheById($model->id, $model->toArray());
                static::unsetByTag();
            });
        }

        static::{Event::DELETED}(function (Model $model) {
            /** @noinspection PhpUndefinedFieldInspection */
            static::unsetByLabel($model->id);
            static::unsetByTag();
        });
    }

    /**
     * @inheritdoc
     */
    final static public function label($id)
    {
        return sprintf('%s:%s', static::tag(), $id);
    }

    /**
     * @inheritdoc
     */
    final static public function tag()
    {
        return static::$TAG ?: get_called_class();
    }

    /**
     * @inheritdoc
     */
    static public function hasInCacheId($id)
    {
        return Cache::has(self::label($id));
    }

    /**
     * @inheritdoc
     */
    static public function hasInCacheTag($tag)
    {
        return Cache::tags(self::tag())->has($tag);
    }

    /**
     * @inheritdoc
     */
    final static public function setTag($tag)
    {
        static::$TAG = $tag;
    }

    /**
     * @inheritdoc
     */
    final static public function setCacheById($id, $value)
    {
        Cache::forever(self::label($id), $value);
    }

    /**
     * @inheritdoc
     */
//    final static public function setCacheByTag($tag, $value)
//    {
//        Cache::tags(self::tag())->forever($tag, $value);
//    }

    /**
     * @inheritdoc
     */
    final static public function getCacheById($id)
    {
        return Cache::get(self::label($id));
    }

    /**
     * @inheritdoc
     */
    final static public function getCacheByTag($tag)
    {
        return Cache::tags(self::tag())->get($tag);
    }

    /**
     * @inheritdoc
     */
    final static public function getFromCacheId($id, Closure $value)
    {
        if (static::hasEnableCache()) {
            if (static::hasInCacheId($id)) {
                $cache = static::getCacheById($id);
            } else {
                $cache = Cache::rememberForever(self::label($id), $value);
            }
        } else {
            $cache = $value();
        }
        return $cache;
    }

    /**
     * @inheritdoc
     */
    final static public function getFromCacheTag($tag, Closure $value)
    {
        if (static::hasEnableCache()) {
            if (static::hasInCacheTag($tag)) {
                $cache = static::getCacheByTag($tag);
            } else {
                $cache = Cache::tags(self::tag())->rememberForever($tag, $value);
            }
        } else {
            $cache = $value();
        }
        return $cache;
    }

    /**
     * @inheritdoc
     */
    final static public function unsetByLabel($id)
    {
        Cache::forget(self::label($id));
    }

    /**
     * @inheritdoc
     */
    final static public function unsetByTag()
    {
        Cache::tags(self::tag())->flush();
    }

    /**
     * @inheritdoc
     */
    final static public function enableCache()
    {
        static::$ENABLED_CACHE = true;
        self::rebootCacheControlTrait();
    }

    /**
     * @inheritdoc
     */
    final static public function disableCache()
    {
        static::$ENABLED_CACHE = false;
        self::rebootCacheControlTrait();
    }

    /**
     * @inheritdoc
     */
    final static public function hasEnableCache()
    {
        return static::$ENABLED_CACHE === true;
    }

    /**
     * Reboot
     */
    final static private function rebootCacheControlTrait()
    {
        static::flushEventListeners();
        static::clearBootedModels();
    }
}
