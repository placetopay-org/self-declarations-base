<?php

namespace FreddieGar\Base\Contracts\Interfaces;

use Illuminate\Database\Query\Builder;

/**
 * Interface FilterBuilderInterface
 * @package FreddieGar\Base\Contracts\Interfaces
 */
interface FilterBuilderInterface
{
    /**
     * @param mixed $query
     * @param array $filters
     * @return Builder
     */
    static public function builder($query, array $filters);
}
