<?php

namespace FreddieGar\Base\Contracts\Commons;

use Exception;
use FreddieGar\Base\Constants\CacheKey;
use FreddieGar\Base\Contracts\Interfaces\EventInterface;
use FreddieGar\Base\Contracts\Interfaces\RepositoryInterface;
use FreddieGar\Base\Repositories\Eloquent\EloquentRepository;
use FreddieGar\Base\Traits\ManagerEventTrait;
use FreddieGar\Base\Traits\RequestLaravelTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Class ManagerLaravel
 *
 * @method array getSelectList()
 *
 * @package FreddieGar\Base\Contracts\Commons
 */
abstract class ManagerLaravel implements RepositoryInterface, EventInterface
{
    use ManagerEventTrait;
    use RequestLaravelTrait;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EloquentRepository
     */
    protected $repository;

    /**
     * @var EntityLaravel
     */
    protected $entity;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @return string
     */
    final protected function tag()
    {
        return get_class($this->entity());
    }

    /**
     * @return string
     */
    final protected function key()
    {
        $keys = func_num_args() > 1 ? func_get_args() : func_get_arg(0);

        if (is_array($keys)) {
            $keyParsed = [];

            foreach ($keys as $key) {
                $keyParsed[] = self::key($key);
            }
            return implode(':', $keyParsed);
        }

        return $keys;
    }

    /**
     * @param $key
     * @param \Closure $value
     * @param null $inTag
     * @return mixed
     */
    final protected function cache($key, \Closure $value, $inTag = null)
    {
        if (is_array($key)) {
            $key = self::key($key);
        }

        $tag = $inTag ? $inTag : $this->tag();

        return Config::get('app.use_cache', true) ? Cache::tags($tag)->rememberForever($key, $value) : $value();
    }

    /**
     * @param $group
     * @param $key
     * @param \Closure $value
     * @return mixed
     */
    final protected function cacheByGroup($group, $key, \Closure $value)
    {
        $tag = self::key($this->tag(), $group);

        return self::cache($key, $value, $tag);
    }

    /**
     * @param $key
     * @param \Closure $value
     * @return mixed
     */
    final protected function cacheSelectList($key, \Closure $value)
    {
        return self::cacheByGroup(CacheKey::SELECT_LIST, $key, $value);
    }

    /**
     * @param null $key
     * @param null $inTag
     * @return mixed
     */
    final protected function cacheFlush($key = null, $inTag = null)
    {
        $tag = $inTag ? $inTag : $this->tag();

        if (!is_null($key)) {
            return Cache::tags($tag)->forget($key);
        }

        return Cache::tags($tag)->flush();
    }

    /**
     * @param $group
     * @param null $key
     * @param null $inTag
     * @return mixed
     */
    final protected function cacheFlushByGroup($group, $key = null, $inTag = null)
    {
        $tag = $inTag ? self::key($inTag, $group) : self::key($this->tag(), $group);

        return self::cacheFlush($key, $tag);
    }

    /**
     * @return mixed
     */
    final protected function cacheFlushSelectList()
    {
        return self::cacheFlushByGroup(CacheKey::SELECT_LIST);
    }

    /**
     * @param Request $request
     * @return $this|Request
     */
    final protected function request(Request $request = null)
    {
        if (func_num_args() > 0) {
            $this->request = $request;

            return $this;
        }

        return $this->request;
    }


    /**
     * @param mixed $repository
     * @return $this|EloquentRepository
     */
    protected function repository($repository = null)
    {
        if (func_num_args() > 0) {
            $this->repository = $repository;

            return $this;
        }

        return $this->repository;
    }

    /**
     * @param null $entity
     * @return $this|EntityLaravel
     */
    protected function entity($entity = null)
    {
        if (func_num_args() > 0) {
            $this->entity = $entity;

            return $this;
        }

        return $this->entity;
    }

    /**
     * @param null $query
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    final public function query($query = null)
    {
        if (func_num_args() > 0) {
            $this->query = $query;

            return $this;
        }

        return $this->query ?? $this->repository()->query();
    }

    /**
     * @return EntityLaravel|Collection
     */
    final public function where()
    {
        return $this->response($this->query()->where(func_get_arg(0))->get()->toArray());
    }

    /**
     * Get entity dummy, it can use when need a response not null
     * setting default data
     * @param array $attributes
     * @return $this|static
     */
    public function dummy(array $attributes)
    {
        //TODO: Use dummy attribute in load defined constant to do it
        return $this->entity()->load(array_merge($attributes, ['dummy' => true]));
    }

    /**
     * @param $method
     * @param $parameters
     * @return array|static
     * @throws Exception
     */
    public function __call($method, $parameters)
    {
        throw new Exception("Method [$method] must be implemented in " . static::class);
    }

    /**
     * @param array $attributes
     * @return Collection|EntityLaravel
     */
    public function response($attributes = null)
    {
        if (is_null($attributes)) {
            return null;
        }

        return !isset($attributes['id'])
            ? collect($this->entity()->loadMultiple($attributes))
            : $this->entity()->load($attributes);
    }

    /**
     * @param array $attributes
     * @return EntityLaravel|mixed
     * @throws Exception
     */
    public function create(array $attributes = [])
    {
        if (!$_new = $this->repository()->create($this->entity()->load($attributes)->toArray())) {
            throw new Exception(trans('exceptions.model_not_saved'));
        }

        $entity = $this->getById($_new['id']);

        $this->created($entity);

        return $entity;
    }

    /**
     * @param array $items
     * @return Collection
     * @throws Exception
     */
    public function createMany(array $items = [])
    {
        $instances = [];

        foreach ($items as $attributes) {
            $instances[] = $this->create($attributes);
        }

        return collect($instances);
    }

    /**
     * @param int $id
     * @param array $attributes
     * @return EntityLaravel|mixed
     * @throws Exception
     */
    public function update($id, array $attributes = [])
    {
        $_old = $this->getById($id);

        if (!$this->repository()->update($id, $this->entity()->load($attributes)->toArray())) {
            throw new Exception(trans(('exceptions.model_not_saved')));
        }

        $this->updated($_old);

        return $this->getById($id);
    }

    /**
     * @param int $id
     * @return int
     * @throws Exception
     */
    public function delete($id)
    {
        if (!$id) {
            return 0;
        }

        $this->deleted($this->getById($id));

        return $this->repository()->delete($id);
    }

    /**
     * @param array ...$ids
     * @return int
     * @throws Exception
     */
    public function deleteMany(...$ids)
    {
        $deleteRows = 0;

        foreach (func_num_args() > 1 ? func_get_args() : func_get_arg(0) as $id) {
            $deleteRows += self::delete($id);
        }

        return $deleteRows;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id)
    {
        return self::cache($id, function () use ($id) {
            return $this->response($this->repository()->getById($id));
        });
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function getAll(array $columns = ['*'])
    {
        return self::cache(CacheKey::ALL, function () use ($columns) {
            return $this->response($this->repository()->getAll($columns));
        });
    }
}
