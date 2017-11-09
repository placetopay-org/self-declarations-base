<?php

namespace FreddieGar\Base\Constants;

/**
 * Interface FilterType
 * @package FreddieGar\Base\Constants
 */
interface FilterType
{
    const TEXT = 'text';
    const NUMBER = 'number';
    const EMAIL = 'email';
    const SELECT = 'select';
    const DATE = 'date';
    const BETWEEN = 'between';
    const BETWEEN_MIN_SUFFIX = '_min';
    const BETWEEN_MAX_SUFFIX = '_max';
    const SMART = 'smart';
}
