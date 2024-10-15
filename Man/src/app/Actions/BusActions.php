<?php

namespace App\Actions;

use App\Interfaces\BusActionsInterface;
use App\Entities\Trajet;

class BusActions implements BusActionsInterface
{
    public function demarrerTrajet(Trajet $trajet): void{
        // Enregistrement des ticks sur les arrêts
        echo "Démarrage du bus\n";
    }

    public function avancer(): void
    {
        echo "Avancement du bus\n";
    }

    public function chargerPersonnes(array $personnes): void
    {
        foreach ($personnes as $person) {
            echo "Chargement de la personne {$person->nom} dans le bus\n";
        }
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
