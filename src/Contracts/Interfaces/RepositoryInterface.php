<?php

namespace FreddieGar\Base\Contracts\Interfaces;

use FreddieGar\Base\Contracts\Commons\EntityLaravel;
use Illuminate\Database\Eloquent\Builder;

interface RepositoryInterface
{
    /**
     * @param array $attributes
     * @return mixed|EntityLaravel
     */
    public function create(array $attributes = []);

    /**
     * @param int $id
     * @param array $attributes
     * @return mixed|EntityLaravel
     */
    public function update($id, array $attributes = []);

    /**
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * @param int $id
     * @return EntityLaravel
     */
    public function getById($id);

    /**
     * @param array $columns
     * @return mixed
     */
    public function getAll(array $columns = ['*']);

    /**
     * @return Builder
     */
    public function query();

    /**
     * @param $id
     * @param \Closure $value
     * @return bool
     */
    public function haveInformationRelated($id, $value = null);
}
