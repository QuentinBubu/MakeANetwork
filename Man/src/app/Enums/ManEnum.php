<?php

namespace App\Enums;

enum ManEnum
{
    case RUNNING;
    case SUCCEEDED;
    case PAUSED;
    case WAITING_START;
    case UNUNITIALIZED;
}
