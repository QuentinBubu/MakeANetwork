<?php

namespace App\Actions;

use App\Entities\Arret;
use App\Timer\Timer;

trait BusActions
{
    public function demarrerParcours(): void
    {
        $this->parcours->arriveArret($this);
        // Enregistrement des ticks sur les arrêts
        foreach (array_slice($this->parcours->arretsAFaire, 0)  as $arret) {
            $this->calculEtEnregistrementProchainPassage($arret);
            // Attention à calculer tout les n+1 parcours
            /*
                Considérons les parcours BED
                Bus en E
                Personne en D veut aller en B ou E
                Il faut que le bus se soit enregistré dans X temps de nouveau à B et E
                Il doit donc déposer son prochain passage
            */
        }
        echo "Démarrage du bus\n";
    }

    public function calculEtEnregistrementProchainPassage(Arret $arret): void
    {
        $timer = new Timer($this->tickTo($this->parcours, $arret, $this->vitesseDeplacement));
        $arret->addBusEnApproche($this, $timer);
        $this->addTimer($arret, $timer);
    }
}
