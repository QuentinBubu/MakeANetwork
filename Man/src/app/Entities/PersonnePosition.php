<?php

namespace App\Entities;

class PersonnePosition extends Position
{
    public function incrementTick(): void
    {
        $this->tick++;
    }
}
