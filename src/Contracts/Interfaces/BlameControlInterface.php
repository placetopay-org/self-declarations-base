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
    static public function enableBlame();

    /**
     * Enable save created by column
     * @return void
     */
    static public function enableCreatedBy();

    /**
     * Enable update updated by column
     * @return void
     */
    static public function enableUpdatedBy();

    /**
     * Enable update deleted by column
     * @return void
     */
    static public function enableDeletedBy();

    /**
     * Disable blame to all columns
     * @return void
     */
    static public function disableBlame();

    /**
     * Disable save created by column
     * @return void
     */
    static public function disableCreatedBy();

    /**
     * Disable update updated by column
     * @return void
     */
    static public function disableUpdatedBy();

    /**
     * Disable update deleted by column
     * @return void
     */
    static public function disableDeletedBy();

    /**
     * Set user to use in blame columns
     * @param int $id
     * @return void
     */
    static public function setCurrentUserAuthenticated($id);

    /**
     * Get guard used in blame
     * @param string $event
     * @param string $model
     * @return int
     */
    static public function getCurrentUserAuthenticated($event, $model);
}
