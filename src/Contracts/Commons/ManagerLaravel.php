<?php

namespace FreddieGar\Base\Contracts\Commons;

use App\Constants\CacheKey;
use App\Entities\TranslationEntity;
use App\Exceptions\ModelNotSavedException;
use FreddieGar\Base\Contracts\Interfaces\EventInterface;
use FreddieGar\Base\Contracts\Interfaces\RepositoryInterface;
use FreddieGar\Base\Repositories\Eloquent\EloquentRepository;
use FreddieGar\Base\Traits\ManagerEventTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

//    /**
//     * @var mixed
//     */
//    protected $temporalEntity;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    final protected function tag()
    {
        return get_class($this->entity());
    }

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

    final protected function cache($key, \Closure $value, $inTag = null)
    {
        if (is_array($key)) {
            $key = self::key($key);
        }

        $tag = $inTag ? $inTag : $this->tag();

        ff(sprintf("cache()->tags('%s')->get('%s')\n%s", $tag, $key, print_r(Cache::tags($tag)->rememberForever($key, $value), true)));

        return Cache::tags($tag)->rememberForever($key, $value);
    }

    final protected function cacheByGroup($group, $key, \Closure $value)
    {
        $tag = self::key($this->tag(), $group);

        return self::cache($key, $value, $tag);
    }

    final protected function cacheSelectList($key, \Closure $value)
    {
        return self::cacheByGroup(CacheKey::TAG_SELECT_LIST, $key, $value);
    }

    final protected function cacheFlush($key = null, $inTag = null)
    {
        $tag = $inTag ? $inTag : $this->tag();

        if (!is_null($key)) {
//            ff(sprintf("cache()->tags('%s')->forget('%s')", $tag, $key));
            return Cache::tags($tag)->forget($key);
        }

//        ff(sprintf("cache()->tags('%s')->flush()", $tag));
        return Cache::tags($tag)->flush();
    }

    final protected function cacheFlushByGroup($group, $key = null, $inTag = null)
    {
        $tag = $inTag ? self::key($inTag, $group) : self::key($this->tag(), $group);

        return self::cacheFlush($key, $tag);
    }

    final protected function cacheFlushSelectList()
    {
        return self::cacheFlushByGroup(CacheKey::TAG_SELECT_LIST);
    }

    /**
     * @param Request $request
     * @return $this|Request
     */
    final protected function request(Request $request = null)
    {
        if (!is_null($request)) {
            $this->request = $request;
            return $this;
        }

        return $this->request;
    }

    /**
     * @param null $query
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    final public function query($query = null)
    {
        if (!is_null($query)) {
            $this->query = $query;
            return $this;
        }

        return (!is_null($this->query)) ? $this->query : $this->repository()->query();
    }

    /**
     * @return EntityLaravel|Collection
     */
    final public function where()
    {
        return $this->response($this->query()->where(func_get_arg(0))->get()->toArray());
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    final protected function requestAttribute($name = null, $default = null)
    {
        $attributes = $name
            ? $this->request()->input($name, $default)
            : $this->request()->input();

        return $attributes ?: [];
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    final protected function requestFilter($name, $default = null)
    {
        return $this->request()->input($name, $default);
    }

    /**
     * @param array $keys
     * @return array
     */
    final protected function requestExcept(array $keys = [])
    {
        return $this->request()->except($keys);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    final protected function requestAddFilter($name, $value = null)
    {
        $this->request()->merge([$name => $value]);
    }

    /**
     * @param mixed $repository
     * @return $this|EloquentRepository
     */
    protected function repository($repository = null)
    {
        if (!is_null($repository)) {
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
        if (!is_null($entity)) {
            $this->entity = $entity;
            return $this;

        }

        return $this->entity;
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
     */
    public function __call($method, $parameters)
    {
        die("Method [$method] must be implemented in " . static::class);
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

        return !isset($attributes['id']) ? collect($this->entity()->loadMultiple($attributes)) : $this->entity()->load($attributes);
    }

    public function create(array $attributes = [])
    {
        $entity = $this->entity()->load($attributes);

        if (!$merge = $this->repository()->create($entity->toArray())) {
            throw new ModelNotSavedException(__METHOD__, $entity);
        }

        $entity = $entity->merge($merge);

        $this->created($entity);

        return $entity;
    }

    /**
     * @param array $items
     * @return \Illuminate\Support\Collection
     */
    public function createMany(array $items = [])
    {
        $instances = [];

        foreach ($items as $attributes) {
            $instances[] = $this->create($attributes);
        }

        return collect($instances);
    }

    public function update($id, array $attributes = [])
    {
        // Only attributes to update
        $entity = $this->entity()->load($attributes);
        $entity->id($id);

        if (!$this->repository()->update($id, $entity->toArray(), 'toArray')) {
            throw new ModelNotSavedException(__METHOD__, $entity);
        }

        // Load all values to cache clear
        $entity = $this->getById($id);

        $this->updated($entity);

        return $entity;
    }

    public function delete($id)
    {
        if (is_null($id)) {
            return 0;
        }

        $this->deleted($this->getById($id));

        return $this->repository()->delete($id);
    }

    public function deleteMany(...$ids)
    {
        $deleteRows = 0;

        foreach (func_num_args() > 1 ? func_get_args() : func_get_arg(0) as $id) {
            $deleteRows += self::delete($id);
        }

        return $deleteRows;
    }

//    public function ______fill(array $attributes = [], $id = null)
//    {
//        $this->temporalEntity = $this->entity()->load($attributes, true);
//        if (!is_null($id)) {
//            $this->temporalEntity->id($id);
//        }
//
//        return $this;
//    }
//
//    public function ________save()
//    {
//        if (is_null($this->temporalEntity)) {
//            throw new \Exception('Entity is empty, impossible save it.');
//        }
//
//        try {
//            $id = $this->temporalEntity->id();
//            $attributes = $this->temporalEntity->toArray();
//
//            unset($this->temporalEntity);
//
//            if (is_numeric($id)) {
//                return self::update($id, $attributes);
//            } else {
//                return self::create($attributes);
//            }
//        } catch (\Exception $e) {
//            return false;
//        }
//    }

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

    public function getById($id)
    {
        return self::cache($id, function () use ($id) {
            return $this->response($this->repository()->getById($id));
        });
    }

    public function haveInformationRelated($id, $value = null)
    {
        if (is_null($value)) {
            $value = function () use ($id) {
                return $this->repository()->haveInformationRelated($id);
            };
        }

        return self::cache(self::key(CacheKey::HAVE_INFORMATION_RELATED, $id), $value);
    }
}
