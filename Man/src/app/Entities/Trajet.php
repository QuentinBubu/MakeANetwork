<?php
namespace App\Entities;

use App\Interfaces\StateInterface;

/**
 * @Entity
 *
 * Représente un trajet, qui est un ensemble de routes permettant de se rendre d'un point A à un point B.
 * Un trajet peut comporter plusieurs routes et permet de calculer le prochain arrêt à partir d'un arrêt donné.
 */
class Trajet implements StateInterface
{
    public string $nom;

    /**
     * Liste des routes qui composent le trajet.
     * 
     * @var Route[]
     */
    public array $routes;

    /**
     * L'arrêt de départ du trajet.
     * 
     * @var Arret
     */
    public Arret $depart;

    /**
     * L'arrêt d'arrivée du trajet.
     * 
     * @var Arret
     */
    public Arret $arrivee;

    /**
     * Distance totale du trajet, qui est la somme des distances de toutes les routes.
     * 
     * @var int
     */
    public int $distance;

    /**
     * Constructeur de la classe Trajet.
     *
     * @param string $nom Nom du trajet.
     * @param array $route Liste des routes qui composent le trajet.
     * @param Arret $depart L'arrêt de départ.
     * @param Arret $arrivee L'arrêt d'arrivée.
     * @param int $distance La distance totale du trajet.
     */
    public function __construct(string $nom, array $route, Arret $depart, Arret $arrivee, int $distance)
    {
        $this->nom = $nom;
        $this->routes = $route;
        $this->depart = $depart;
        $this->arrivee = $arrivee;
        $this->distance = $distance;
    }

    /**
     * Trouve la route correspondant à un arrêt de départ donné, en évitant de revisiter un arrêt déjà visité.
     *
     * Complexité: O(n * m) où n est le nombre de routes et m est le nombre d'arrêts par route.
     * Cette méthode parcourt toutes les routes et tous les arrêts de chaque route à la recherche d'un arrêt non visité.
     *
     * @param Arret $depart L'arrêt de départ.
     * @param array $arretsVisites Liste des arrêts déjà visités.
     * @return Route La route qui correspond à l'arrêt de départ.
     * @throws \Exception Si aucune route valide n'est trouvée.
     */
    public function getRouteFromArret(Arret $depart, array $arretsVisites): Route
    {
        foreach ($this->routes as $route) {
            $arrets = $route->arrets;
            $indexDepart = array_search($depart, $arrets, true);

            // Si l'arrêt de départ existe dans la route
            if ($indexDepart !== false) {
                // Calcul de l'indice du prochain arrêt dans un parcours circulaire
                $indexProchainArret = ($indexDepart + 1) % count($arrets);
                $prochainArret = $arrets[$indexProchainArret];

                // Vérifie que le prochain arrêt n'a pas déjà été visité
                if (!in_array($prochainArret, $arretsVisites, true)) {
                    return $route;
                }
            }
        }
        throw new \Exception("Aucune route trouvée pour l'arrêt {$depart->nom}");
    }

    /**
     * Trouve le prochain arrêt à partir d'un arrêt donné.
     *
     * Complexité: O(n * m) où n est le nombre de routes et m est le nombre d'arrêts par route.
     * La méthode parcourt chaque route et recherche l'arrêt actuel dans la liste des arrêts.
     *
     * @param Arret $arretActuel L'arrêt actuel d'où on veut obtenir le prochain arrêt.
     * @return Arret|null Le prochain arrêt si trouvé, sinon null.
     */
    public function getProchainArret(Arret $arretActuel): ?Arret
    {
        foreach ($this->routes as $route) {
            $arrets = $route->arrets;
            $indexArretActuel = array_search($arretActuel, $arrets, true);

            if ($indexArretActuel !== false) {
                // Calcul de l'indice du prochain arrêt de façon circulaire
                $indexProchainArret = ($indexArretActuel + 1) % count($arrets);
                return $arrets[$indexProchainArret];
            }
        }
        return null;
    }

    /**
     * Exporte les informations du trajet sous forme de tableau.
     * 
     * @return array Un tableau contenant les informations du trajet.
     */
    public function export(): array
    {
        return [
            'nom' => $this->nom,
            'depart' => $this->depart->nom,
            'arrivee' => $this->arrivee->nom,
            'distance' => $this->distance,
            'routes' => array_map(
                function ($route) {
                    return $route->nom;
                },
                $this->routes
            ),
        ];
    }
}
