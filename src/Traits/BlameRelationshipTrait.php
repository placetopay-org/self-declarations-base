<?php

namespace FreddieGar\Base\Traits;

use App\Models\Authenticator;

trait BlameRelationshipTrait
{

    public function createdBy()
    {
        return $this->belongsTo(Authenticator::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Authenticator::class, 'updated_by');
    }
}
