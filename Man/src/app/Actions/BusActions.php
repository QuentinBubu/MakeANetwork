<?php

namespace App\Actions;

use App\Entities\Parcours;
use App\Entities\Personne;
use App\Enums\BusStateEnum;

trait BusActions
{
    public function demarrerParcours(): void{
        // Enregistrement des ticks sur les arrêts
        $this->state = BusStateEnum::DEPART;
        foreach ($this->parcours->arretsAFaire as $arret) {
            $arret->addBusEnApproche($this, $this->tickTo($this->parcours, $arret));
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

    public function avancer(): void
    {
        echo "Avancement du bus\n";
    }

    public function chargerPersonne(Personne $personne): bool
    {
        if ($this->getPlaceDisponible() === 0) {
            return false;
        }
        // Attention : montée et descente en même temps : cas plus de personnes descendentes que montentes
        $this->personnes[] = $personne;

        return true;
    }

    public function dechargerPersonnes(): void
    {
        echo "Déchargement des personnes du bus\n";
    }

    public function __toString(): string
    {
        return 'Actions du bus';
    }
}
