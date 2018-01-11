<?php

namespace Amari\Contracts;

interface JsonableContract
{
    /**
     * Initialize json field cast to other objects.
     *
     * @param string $field
     *
     * @return JsonCastContract
     */
    public function jsonCast(string $field): JsonCastContract;
    
    public function getJsonStructure(): array;
}
