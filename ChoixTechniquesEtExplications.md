# Choix techniques et explication d'algorithme

## Introduction

Dans ce document, nous allons expliquer les choix techniques que nous avons faits pour notre projet. Nous allons également expliquer les algorithmes que nous avons utilisés pour résoudre le problème posé.

## Choix techniques

### Langage de programmation

Pour ce faire, nous avons choisi d'utiliser le langage de programmation PHP et ce pour 2 principales raisons. En premier lieu, car nous avons une certaine expérience dans ce langage, donc nous n'avons pas eu besoin de temps d'apprentissage.

En second lieu, car en le faisant en Web, nous pouvons vraiment séparer le back-end du front-end, ce qui est un avantage pour la maintenance du code.
De plus, la gestion graphiques est, selon nous, beaucoup plus simple.

Pour la communication entre les 2, nous utilisons des sockets JavaScript et transmettons ou de simple signaux, ou des messages plus complexes sous forme de JSON.

### Structure du projet

On retrouve de multiples dossiers dans le projet, chacun ayant une fonction bien précise :
- `app` : Contient les classes de l'application
- `client` : Contient les fichiers de l'interface graphique
- `data` : Contient les données par défaut de la simulation
- `tests` : Contient les tests unitaires
- `vendor` : Contient les dépendances du projet
- `webserver` : Contient le serveur web servant aux sockets

Nous avons à la racine du projet les fichiers `client.html` et `man` qui sont respectivement l'interface graphique et le point d'entrée de l'application.

### Tests unitaires

Nous avons utilisé PHPUnit pour les tests unitaires. Cependant, nous n'avons pas encore vraiment bien implémenté ces tests. Nous les utilisions très régulièrement au début du projet, mais nous avons arrêté de les utiliser pour des raisons de temps malgré leur importance.

### Interface graphique

Pour visualiser les données de la simulation, nous avons choisi d'utiliser une interface graphique en Web avec un `canva`. Cela nous permet de facilement afficher les données et de les manipuler.

### Logs

Nous avons implémenté un système de logs qui permet de suivre l'évolution de la simulation en temps réel. Il est possible de spécifier un fichier de sortie pour les logs, par défaut ils sont sur la sortie standard. Il est possible de spécifier le niveau de verbose pour les logs (de 0 à 3).
0 étant le niveau le plus bas, et 3 le plus haut.

## Explication d'algorithme

### Algorithme de calcul du trajet le plus court

Pour calculer le trajet le plus court, nous avons utilisé l'algorithme de Dijkstra. Cet algorithme permet de trouver le chemin le plus court entre un sommet de départ (les arrêts pour nous) et tous les autres sommets d'un graphe pondéré (les routes).

Nous avons préféré cet algorithme à l'algorithme de Bellman-Ford car il est plus rapide étant donné que nous n'avons pas de poids négatifs.
La complexité de l'algorithme de Dijkstra est de `O(n log v)` avec `n` le nombre de sommets et `v` le nombre d'arêtes alors que Bellman-Ford est de `O(n * v)`.

Par rapport à l'algorithme A*, nous avons préféré Dijkstra car il est plus simple à implémenter. Nous prennons en compte déjà de nombreux paramètres (temps, distance, position des bus, file, etc.) et nous ne voulions pas ajouter de complexité supplémentaire.

Cependant, il est vrai que cet algorithme aurait pu être plus performant, mais nous avons préféré la simplicité.

### Algorithme pour le calcul du plus rapide pour les passagers

```php
/**
 * Trouve le meilleur chemin entre deux arrêts en utilisant l'algorithme de Dijkstra.
 * 
 * @param Personne $personne La personne pour qui nous calculons le chemin.
 * @param Arret $arretFrom L'arrêt de départ.
 * @param Arret $arretTo L'arrêt d'arrivée.
 * 
 * @return array Le chemin optimal sous forme de tableau d'étapes (avec le bus à prendre, l'arrêt de montée, et l'arrêt de descente).
 * 
 * @throws \RuntimeException Si un chemin ne peut pas être trouvé ou si une boucle est détectée.
 * 
 * Complexité: O(E * log(V)), où E est le nombre d'arcs (liens entre arrêts) et V est le nombre d'arrêts.
 */
public static function findBestPath(Personne $personne, Arret $arretFrom, Arret $arretTo): array
{
    // Initialisation des variables pour l'algorithme de Dijkstra
    $distances = array_fill_keys(array_keys(Arrets::$arrets), INF);  // Complexité : O(V)
    $distances[$arretFrom->nom] = 0;
    $previousArrets = [];  // Complexité : O(V)
    $busTaken = [];        // Complexité : O(V)
    $queue = new SplPriorityQueue(); // Priorité de la file d'attente

    $queue->insert($arretFrom->nom, 0); // Complexité : O(log(V))

    Message::log("Début de l'algorithme de Dijkstra depuis l'arrêt de départ : {$arretFrom->nom}", Message::DEBUG_DETAIL);

    // Exécution de l'algorithme de Dijkstra pour trouver les distances minimales
    while (!$queue->isEmpty()) {  // Complexité : O(V)
        $currentArretNom = $queue->extract();  // Complexité : O(log(V))

        if ($currentArretNom === $arretTo->nom) {
            Message::log("Arrêt destination atteint : {$arretTo->nom}", Message::DEBUG_ALL);
            break;
        }

        Message::log("Traitement de l'arrêt : {$currentArretNom}, Distance actuelle : {$distances[$currentArretNom]}", Message::DEBUG_ALL);

        $currentArret = Arrets::getArret($currentArretNom); 

        /** @var stdClass $voisin */
        foreach ($currentArret->getNeighbors() as $voisin) {  // Complexité : O(n) où n est le nombre de voisins
            /** @var \App\Entities\Bus $bus */
            foreach (Bus::$buses as $bus) {  // Complexité : O(m) où m est le nombre de bus
                if (!$bus->peutDesservir($currentArret, $voisin->arret)) {  // Vérification si le bus dessert le voisin
                    Message::log("   -> Bus {$bus->type} parcours {$bus->getParcours()->nom} ne dessert pas {$voisin->arret->nom} depuis {$currentArretNom}", Message::DEBUG_ALL);
                    continue;
                }

                $route = $currentArret->getRouteTo($voisin->arret);
                $time = $route->distance * $bus->vitesseDeplacement; // Calcul du temps de trajet

                $newDistance = $distances[$currentArretNom] + $time;  // Calcul de la nouvelle distance

                Message::log("   -> Tentative de mise à jour pour voisin : {$voisin->arret->nom} avec bus {$bus->type} (Distance : {$newDistance})", Message::DEBUG_ALL);

                if ($newDistance < $distances[$voisin->arret->nom]) {  // Mise à jour des distances
                    $distances[$voisin->arret->nom] = $newDistance;
                    $previousArrets[$voisin->arret->nom] = $currentArretNom;
                    $busTaken[$voisin->arret->nom] = $bus;
                    $queue->insert($voisin->arret->nom, -$newDistance);  // Complexité : O(log(V))
                    Message::log("   -> Mise à jour réussie pour {$voisin->arret->nom} avec bus {$bus->type}. Nouvelle distance : {$newDistance}", Message::DEBUG_ALL);
                } else {
                    Message::log("   -> Non mis à jour : distance existante plus courte ou boucle détectée.", Message::DEBUG_ALL);
                }
            }
        }

        Message::log("Fin du traitement de l'arrêt : {$currentArretNom}", Message::DEBUG_ALL);
    }

    // Vérifier si le chemin complet est atteint
    if (!isset($previousArrets[$arretTo->nom]) && $arretFrom->nom !== $arretTo->nom) {
        throw new \RuntimeException("Impossible de rejoindre l'arrêt de départ. Dernier arrêt atteint : {$currentArretNom}");
    }

    Message::log("Fin de l'algorithme de Dijkstra", Message::DEBUG_DETAIL);

    // Reconstruction du chemin en remontant les précédents arrêts
    $path = [];
    $visitedArrets = [];
    $arret = $arretTo->nom;
    $previousBus = null;

    Message::log("Reconstruction du chemin depuis l'arrêt destination : {$arretTo->nom}", Message::DEBUG_DETAIL);
    $maxSteps = count(Arrets::$arrets);  // Le nombre maximal d'étapes correspond au nombre d'arrêts
    $steps = 0;

    while ($arret !== $arretFrom->nom) {  // Complexité : O(V)
        if (isset($visitedArrets[$arret])) {
            throw new \RuntimeException("Boucle détectée lors de la reconstruction du chemin à l'arrêt : {$arret}");
        }
        $visitedArrets[$arret] = true;

        if (++$steps > $maxSteps) {
            throw new \RuntimeException("Nombre maximal d'étapes dépassé lors de la reconstruction du chemin.");
        }

        if (!isset($previousArrets[$arret])) {
            throw new \RuntimeException("Erreur lors de la reconstruction : Pas de précédent pour l'arrêt {$arret}. Chemin incomplet.");
        }

        $previous = $previousArrets[$arret];
        $currentBus = $busTaken[$arret] ?? null;

        // Vérifiez si le bus est le même que pour l'arrêt précédent
        if ($currentBus !== $previousBus && $currentBus !== null) {
            $personne->setSignalDescente(Arrets::getArret($arret));
        }

        $path[] = [
            'busAPrendre' => $currentBus,
            'arretMontee' => $previous,
            'arretDescente' => $arret
        ];

        Message::log("Étape : Bus {$currentBus->type} de {$previous} à {$arret}", Message::DEBUG_ALL);

        $previousBus = $currentBus; // Mise à jour du bus précédent pour l'étape suivante
        $arret = $previous;
    }

    // Vérification finale pour s'assurer que le chemin est bien complet
    if ($arret !== $arretFrom->nom) {
        throw new \RuntimeException("Impossible de rejoindre l'arrêt de départ. Dernier arrêt atteint : {$arret}");
    }

    Message::log("Fin de la reconstruction du chemin", Message::DEBUG_DETAIL);
    return array_reverse($path);
}
```

### Autres algorithmes

L'ensemble des complexitées algorithmiques sont annotées dans la documentation de chaque méthodes dans le dossier `app`.
