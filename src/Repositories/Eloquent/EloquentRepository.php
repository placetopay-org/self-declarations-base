<?php

namespace FreddieGar\Base\Repositories\Eloquent;

use FreddieGar\Base\Contracts\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Model|Builder model
 */
abstract class EloquentRepository implements RepositoryInterface
{
    public function create(array $attributes = [])
    {

        return $this->model->create($attributes)->attributesToArray();
    }

    public function update($id, array $attributes = [])
    {
        return $this->model->findOrFail($id)->update($attributes);
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * @param array $columns
     * @return array
     */
    public function getAll(array $columns = ['*'])
    {
        return $this->model->all($columns)->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->model->findOrFail($id)->toArray();
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    public function haveInformationRelated($id, $value = null)
    {
        // By default entity dont have information related
        return false;
    }
}
