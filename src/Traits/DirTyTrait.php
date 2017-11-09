<?php

namespace FreddieGar\Base\Traits;

trait DirTyTrait
{
    public function isDirty(array $attributes, array $original = [], array $ignored = [])
    {
        return count($this->getDirty($attributes, $original, $ignored)) > 0;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @param array $attributes
     * @param array $original
     * @param array $ignored
     * @return array
     */
    public function getDirty(array $attributes, array $original = [], array $ignored = [])
    {
        $dirty = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ignored)) {
                continue;
            }
            if (!array_key_exists($key, $original)) {
                $dirty[$key] = $value;
            } elseif ($value !== $original[$key] &&
                !$this->originalIsNumericallyEquivalent($attributes[$key], $original[$key])) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    private function originalIsNumericallyEquivalent($current, $original)
    {
        return is_numeric($current) && is_numeric($original) && strcmp((string)$current, (string)$original) === 0;
    }
}
