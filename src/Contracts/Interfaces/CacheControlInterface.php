<?php

namespace FreddieGar\Base\Contracts\Interfaces;

use Closure;

/**
 * Interface CacheControlInterface
 * @package FreddieGar\Base\Constants\Interfaces
 */
interface CacheControlInterface
{
    /**
     * @param int $id
     * @return string
     */
    static public function label($id);

    /**
     * @return string
     */
    static public function tag();

    /**
     * @param $id
     * @return bool
     */
    static public function hasInCacheId($id);

    /**
     * @param $tag
     * @return bool
     */
    static public function hasInCacheTag($tag);

    /**
     * @param $tag
     */
    static public function setTag($tag);

    /**
     * @param int $id
     * @param mixed $value
     */
    static public function setCacheById($id, $value);

    /**
     * @param $tag
     * @param $value
     */
//    final static public function setCacheByTag($tag, $value);

    /**
     * @param $id
     * @return mixed
     */
    static public function getCacheById($id);

    /**
     * @param $tag
     * @return mixed
     */
    static public function getCacheByTag($tag);

    /**
     * @param int $id
     * @param Closure $value
     * @return mixed
     */
    static public function getFromCacheId($id, Closure $value);

    /**
     * @param string $tag
     * @param Closure $value
     * @return mixed
     */
    static public function getFromCacheTag($tag, Closure $value);

    /**
     * @param $id
     */
    static public function unsetByLabel($id);

    /**
     * Eraser cache by tag
     */
    static public function unsetByTag();

    /**
     * Enable cache
     */
    static public function enableCache();

    /**
     * Enable cache
     */
    static public function disableCache();

    /**
     * @return bool
     */
    static public function hasEnableCache();
}
