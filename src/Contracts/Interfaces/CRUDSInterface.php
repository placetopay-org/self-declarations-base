<?php

namespace FreddieGar\Base\Contracts\Interfaces;

/**
 * Interface CRUDSInterface
 * @package FreddieGar\Base\Contracts\Interfaces
 */
interface CRUDSInterface
{
    /**
     * Model create
     * @return array
     */
    public function create();

    /**
     * Read model specific
     * @param int $id
     * @return array
     */
    public function read($id);

    /**
     * Update model specific
     * @param int $id
     * @return array
     */
    public function update($id);

    /**
     * Delete model specific
     * @param int $id
     * @return array
     */
    public function delete($id);

    /**
     * Search in models
     * @return array
     */
    public function show();
}
