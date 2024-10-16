<?php

namespace App\Interfaces;

interface StateInterface
{
    public function export(): array;
    public function restore(array $state): void;
}
