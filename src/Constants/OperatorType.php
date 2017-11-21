<?php

namespace FreddieGar\Base\Constants;

/**
 * Interface OperatorType
 * @package FreddieGar\Base\Constants
 */
interface OperatorType
{
    const EQUALS = '=';
    const NOT_EQUALS = '!=';
    const LIKE = 'like';
    const NOT_LIKE = 'not like';
    const MINOR = '<';
    const MINOR_EQUALS = '<=';
    const MAJOR = '>';
    const MAJOR_EQUALS = '>=';
}
