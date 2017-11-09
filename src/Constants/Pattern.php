<?php

namespace FreddieGar\Base\Constants;

/**
 * Interface Pattern
 * @package FreddieGar\Base\Constants
 */
interface Pattern
{
    /**
     * Pattern for validate telephone
     */
    const TELEPHONE = '/^([0|\+?[0-9]{1,5})?([0-9\s\(\)]{7,})([\(\)\w\d\.\s]+)?$/';

    /**
     * Pattern for validation document
     */
    const DOCUMENT = '/^[^\.|\,|\-|\_|\s]+$/';

    /**
     * Pattern for validate password, so back-end how front-end
     */
    const PASSWORD_UPPERCASE = 'A-Z';
    const PASSWORD_LOWERCASE = 'a-z';
    const PASSWORD_NUMBERS = '\d';
    const PASSWORD_SYMBOLS = '\s\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\=\>\?\@\^\_\{\}\~'; // OWASP except | character

    /**
     * For search via like query
     */
    const QUERY_LIKE_LEFT = '%%%s'; // %example
    const QUERY_LIKE_RIGHT = '%s%%'; // example%
    const QUERY_LIKE = '%%%s%%'; // %example%
}
