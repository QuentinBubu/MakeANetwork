<?php

namespace App\Manager;

use App\Entities\Bus;

class ArretManager
{
    /**
     * @var Bus[]
     */
    protected array $busEnApproche = [];

    public function addBusEnApproche(Bus $bus): void
    {
        $this->busEnApproche[] = $bus;
    }

    public function removeBusEnApproche(Bus $bus): void
    {
        $key = array_search($bus, $this->busEnApproche, true);
        if ($key !== false) {
            unset($this->busEnApproche[$key]);
        }
    }

    public function getBusEnApproche(): array
    {
        return $this->busEnApproche;
    }
}
