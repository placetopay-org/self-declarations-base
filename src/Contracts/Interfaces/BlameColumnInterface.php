<?php

namespace FreddieGar\Base\Contracts\Interfaces;

/**
 * Interface BlameColumnInterface
 * Setters and Getters for BlameColumns
 * @package FreddieGar\Base\Contracts\Interfaces
 */
interface BlameColumnInterface
{
    /**
     * @param int $created_by
     * @return string
     */
    public function createdBy($created_by = null);

    /**
     * @param int $updated_by
     * @return string
     */
    public function updatedBy($updated_by = null);

    /**
     * @param int $deleted_by
     * @return string
     */
    public function deletedBy($deleted_by = null);

    /**
     * @param string $created_at
     * @return string
     */
    public function createdAt($created_at = null);

    /**
     * @param string $updated_at
     * @return string
     */
    public function updatedAt($updated_at = null);

    /**
     * @param string $deleted_at
     * @return string
     */
    public function deletedAt($deleted_at = null);
}
