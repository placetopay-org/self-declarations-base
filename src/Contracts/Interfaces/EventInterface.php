<?php

namespace FreddieGar\Base\Contracts\Interfaces;

interface EventInterface
{
    public function created($entity);

    public function updated($entity);

    public function deleted($entity);
}
