<?php

namespace App\Timer;

use App\Interfaces\TimeInterface;

class Timer implements TimeInterface
{
    private int $remainingTicks = 0;

    public function __construct(int $remainingTicks)
    {
        $this->remainingTicks = $remainingTicks;
        Time::registerClass($this);
    }

    public function getRemainingTicks(): int
    {
        return $this->remainingTicks;
    }

    public function incrementTicks(): void
    {
        $this->remainingTicks++;
    }

    public function incrementTick(): void
    {
        $this->remainingTicks--;
    }

    public function __toString()
    {
        return "Temsp restant : " . $this->remainingTicks;
    }
}
