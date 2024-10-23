<?php

namespace App\Enums;

enum BusStateEnum
{
    case FLUX_VOYAGEURS;
    case ARRET;
    case DEPART;
    case EN_ROUTE;
    case FIN;
}
