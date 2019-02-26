<?php

namespace Ddup\Models;

use Ddup\Models\Traits\CallTableAble;
use Ddup\Models\Traits\DynamicWhereTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;


/**
 * @method Builder whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method Builder orWhere($column, $operator = null, $value = null)
 * @method Builder whereNotIn($column, $values, $boolean = 'and')
 * @method Builder whereBetween($column, array $values, $boolean = 'and', $not = false)
 * @method Builder join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method Builder leftJoin($table, $first, $operator = null, $second = null)
 * @method Builder rightJoin($table, $first, $operator = null, $second = null)
 * @method Builder joinWhere($table, $first, $operator, $second, $type = 'inner')
 * @method Builder orderBy($name, $sort)
 * @method Builder limit($value)
 * @method Builder select($columns = ['*'])
 * @method Collection get($columns = ['*'])
 * @method Builder paginate(int $limit)
 * @method Builder find($id)
 * @method Collection pluck($name, $key = null)
 * @method string implode($name, $glue = '')
 * @method int count($columns = '*')
 * @method mixed min($column)
 * @method mixed max($column)
 * @method mixed sum($column)
 * @method mixed value($name)
 * @method Builder fill(array $attributes)
 * @method int insertGetId(array $values)
 * @method Builder create(array $values)
 * @method Builder update(array $attributes = [], array $options = [])
 * @method bool save(array $options = [])
 * @method int|bool decrement($name, $value)
 * @method int|bool increment($name, $value)
 * @method bool delete($id = null)
 * @method Builder from($table)
 * @method string toSql()
 */
class Model extends EloquentModel
{
    /**
     * @var Collection
     */
    private $request;
    private $calls = [];


    use DynamicWhereTrait;
    use CallTableAble;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    protected function getRequest()
    {
        $request = $this->request ?: request();

        return new Collection($request);
    }

    public function dateIn($field_name = null)
    {
        if (is_null($field_name)) $field_name = self::f(self::CREATED_AT);

        $this->calls[] = function (Builder $query) use ($field_name) {
            return $this->fillDateRange($query, $this->getRequest(), $field_name);
        };

        return $this;
    }

    public function dynamicWhere($definds = [])
    {
        if (!is_array($definds)) {
            $arguments = func_get_args();
            $definds   = [];

            foreach ($arguments as $name) {
                $definds[] = [$name, $name];
            }
        }

        $this->calls[] = function (Builder $query) use ($definds) {
            return $this->fillWhere($query, $definds, $this->getRequest());
        };

        return $this;
    }

    public function dynamicWhereLike($name, $request_key = null)
    {
        $request_key = is_null($request_key) ? $name : $request_key;

        return $this->dynamicWhere([
            [
                $name, 'like', $request_key
            ]
        ]);
    }

    public function __call($method, $parameters)
    {
        $query = $this->newQuery();

        foreach ($this->calls as $call) {
            $query = $call($query);
        }

        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }

        return $this->forwardCallTo($query, $method, $parameters);
    }
}


