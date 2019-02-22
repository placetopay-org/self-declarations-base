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
     * @param int $createdBy
     * @return string
     */
    public function createdBy($createdBy = null);

    /**
     * @param int $updatedBy
     * @return string
     */
    public function updatedBy($updatedBy = null);

    /**
     * @param int $deletedBy
     * @return string
     */
    public function deletedBy($deletedBy = null);

    /**
     * @param string $createdAt
     * @return string
     */
    public function createdAt($createdAt = null);

    /**
     * @param string $updatedAt
     * @return string
     */
    public function updatedAt($updatedAt = null);

    /**
     * @param string $deletedAt
     * @return string
     */
    public function deletedAt($deletedAt = null);
}
