<?php

namespace FreddieGar\Base\Contracts\Interfaces;

/**
 * Interface BlameControlInterface
 * @package FreddieGar\Base\Contracts\Interfaces
 */
interface BlameControlInterface
{
    /**
     * Enable blame to all columns
     * @return void
     */
    public static function enableBlame();

    /**
     * Enable save created by column
     * @return void
     */
    public static function enableCreatedBy();

    /**
     * Enable update updated by column
     * @return void
     */
    public static function enableUpdatedBy();

    /**
     * Enable update deleted by column
     * @return void
     */
    public static function enableDeletedBy();

    /**
     * Disable blame to all columns
     * @return void
     */
    public static function disableBlame();

    /**
     * Disable save created by column
     * @return void
     */
    public static function disableCreatedBy();

    /**
     * Disable update updated by column
     * @return void
     */
    public static function disableUpdatedBy();

    /**
     * Disable update deleted by column
     * @return void
     */
    public static function disableDeletedBy();

    /**
     * Set user to use in blame columns
     * @param int $id
     * @return void
     */
    public static function setCurrentUserAuthenticated($id);

    /**
     * Get guard used in blame
     * @param string $event
     * @param string $model
     * @return int
     */
    public static function getCurrentUserAuthenticated($event, $model);
}
