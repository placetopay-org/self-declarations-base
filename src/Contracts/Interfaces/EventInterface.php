<?php

namespace FreddieGar\Base\Contracts\Interfaces;

use FreddieGar\Base\Contracts\Commons\EntityLaravel;
use Illuminate\Database\Eloquent\Builder;

interface EventInterface
{
    public function created($entity);

    public function updated($entity);

    public function deleted($entity);
}
