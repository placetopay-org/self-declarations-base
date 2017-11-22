<?php

namespace FreddieGar\Base\Contracts\Commons;

use FreddieGar\Base\Constants\HttpMethod;
use FreddieGar\Base\Constants\JsonApiName;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Neomerx\JsonApi\Encoder\Encoder;

/**
 * Class ManagerLaravel
 * @package FreddieGar\Base\Contracts\Commons
 */
abstract class ManagerJsonApi
{
    use ProvidesConvenienceMethods;

    const WRAPPER_FILTERS = JsonApiName::FILTER;
    const WRAPPER_ATTRIBUTES = JsonApiName::DATA . '.' . JsonApiName::ATTRIBUTES;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var mixed
     */
    protected $repository;

    /**
     * Model that manage manager
     * @return mixed
     */
    abstract protected function model();

    /**
     * @param Request $request
     * @return Request
     */
    final protected function request(Request $request = null)
    {
        if (!is_null($request)) {
            $this->request = $request;
        }

        return $this->request;
    }

    /**
     * @return string
     */
    final protected function requestMethod()
    {
        return $this->request()->method();
    }

    /**
     * @return string
     */
    final protected function requestIp()
    {
        return $this->request()->ip();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    final protected function requestInput($name = null, $default = null)
    {
        return $name ? $this->request()->input($name, $default) : $this->request()->all();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    final protected function requestAttribute($name = null, $default = null)
    {
        $attributes = $name
            ? $this->request()->input(self::WRAPPER_ATTRIBUTES . '.' . $name, $default)
            : $this->request()->input(self::WRAPPER_ATTRIBUTES);

        return $attributes ?: [];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    final protected function requestFilter($name, $default = null)
    {
        return $this->request()->input(self::WRAPPER_FILTERS . '.' . $name, $default);
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
        $this->request()->merge([
            self::WRAPPER_FILTERS => [$name => $value]
        ]);
    }

    /**
     * Valid data in request
     * return $this
     */
    final public function requestValidate()
    {
        $validator = $this->getValidationFactory()->make(
            $this->requestAttribute(),
            $this->removeRulesThatNotApply($this->rules()),
            $this->messages()
        );

        if ($validator->fails()) {
            $this->throwValidationException($this->request(), $validator);
        }

        return $this;
    }

    /**
     * Remove rules to fields that are not in request
     * @param $rules
     * @return mixed
     */
    final private function removeRulesThatNotApply($rules)
    {
        $attributes = $rules[JsonApiName::DATA][JsonApiName::ATTRIBUTES];

        if ($this->requestMethod() === HttpMethod::PATCH) {
            foreach ($attributes as $field => $_rules) {
                if (!$this->requestAttribute($field)) {
                    unset($attributes[$field]);
                }
            }
        }

        return $attributes;
    }

    /**
     * @param mixed $repository
     * @return mixed
     */
    final protected function repository($repository = null)
    {
        if (!is_null($repository)) {
            $this->repository = $repository;
        }

        return $this->repository;
    }

    /**
     * @param int $id
     * @param string $relationship
     * @return array
     */
    final public function relationship($id, $relationship)
    {
        $method = camel_case($relationship);

        return static::{$method}($id);
    }

    /**
     * @param $resource
     * @return string
     */
    final static public function response($resource)
    {
        $encoder = Encoder::instance(static::schemas(), encoderOptions());
        return $encoder->encodeData($resource);
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    static protected function schemas()
    {
        return [];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function messages()
    {
        return [];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected function filters()
    {
        return [];
    }

    /**
     * @param string $relationship
     * @param array $arguments
     * @throws \Exception
     */
    public function __call($relationship, array $arguments = [])
    {
        throw new \Exception(trans('exceptions.relationship_not_found', compact('relationship')));
    }
}
