<?php

namespace FreddieGar\Base\Constants;

/**
 * Interface BlameEvent
 * @package FreddieGar\Base\Constants
 */
interface Event
{
    const BOOTING = 'booting';
    const SAVING = 'saving';
    const CREATING = 'creating';
    const CREATED = 'created';
    const UPDATING = 'updating';
    const UPDATED = 'updated';
    const DELETING = 'deleting';
    const DELETED = 'deleted';
    const RESTORING = 'restoring';
    const RESTORED = 'restored';
    const SAVED = 'saved';
    const BOOTED = 'booted';
}
