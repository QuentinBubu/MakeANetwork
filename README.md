# Make A Network

## Auteurs

- Grail Liam
- Buffard Quentin

## Liste des fonctionnalités implémentées fonctionnelles

Nous sommes partis sur le fait qu'il faut que le projet soit flexible et modulable. Nous avons donc implémenté un système de configuration qui permet de modifier les paramètres du projet sans avoir à modifier le code.

Actuellement, nous pouvons donc modifier les paramètres suivants :
- Le nombre de personnes (avec l'ajout / suppression de nouvelles personnes avec de nouveaux trajets) (Olvl1_1 et Olvl2_1)
- Le nombre de bus (avec l'ajout / suppression de nouveaux types de bus avec de nouveaux parcours) (Olvl1_2 et Olvl2_2)
- Le nombre d'arrêts (avec l'ajout / suppression de nouveaux arrêts)
- Les routes entre les arrêts (avec l'ajout / suppression de nouvelles routes) (Olvl7)
- Les parcours des bus (avec l'ajout / suppression de nouveaux parcours avec des arrêts spécifiques)

Tout est donc modifiable dans le dossier `data` si l'on exécute le programme en mode ligne de commande ou via l'interface graphique en mode Web.

Au niveau algorithmique, nous avons implémenté :
- La gestion du temps (R1)
- La terminaison de l'algorithme (R2)
- Le déplacement des bus (R3)
- Le remplissage et vidage des bus à chaque unité de temps (R4)
- Le tri des personnes dans l'ordre de montée / descente d'un bus par unité de temps d'arrivée et par ordre aphanumérique (R5)
- Les personnes choisissent leur trajet pour optimiser leur temps de trajet suivant les files d'attentes et positions des bus (R6)
- La vision graphique (R7)
- La possibilité de retour arrière dans le temps sur la vision graphique (R8)

De plus, nous pouvons avec l'inteface graphique, importer et exporter des fichiers d'état de la simulation pour facilement pouvoir reprendre les visuels.

Nous pouvons également utiliser les boutons `forward` et `backward` pour avancer ou reculer dans le temps de la simulation avec des protections (message d'erreur si l'on veut aller en dessous de 0, avancement automatique si l'état suivant n'est pas encore calculé).

Cependant, il est important de noter que le déplacement dans le temps nécessite de mettre la simulation en pause.

Il est possible de lancer l'application depuis plusieurs clients sur un même serveur, ils se synchronisent automatiquement.

Nous avons également implémenté un système de logs qui permet de suivre l'évolution de la simulation en temps réel. Il est possible de spécifier un fichier de sortie pour les logs, par défaut ils sont sur la sortie standard. Il est possible de spécifier le niveau de verbose pour les logs (de 0 à 3).

## Liste fonctionnalités fonctionnant mal ou pas

- L'interface graphique ne supporte pas toutes les fonctionnalités offertes par le serveur et ne démare pas toujours comme il le faut
- Les fonctinalités de hover et click du canvas de l'interface ne fonctionnent correctement que si l'utilisateur est tout en haut de la page 
- les popups informatives des états des arrets sont rudimentaires
- L'importation des fichiers d'état ne fonctionne pas encore
- Les tests unitaires ne sont pas encore vraiment bien implémentés

## Liste des fonctionnalités non implémentées

L'on pourrait ajouter de nombreuses fonctionnalités, telles que :
- La possibilité de modifier les paramètres en temps réel
- La vérification de la validité des données
- Avoir une simulation par client
- Choix du niveau de verbose par la ligne de commande

## Lancement du programme

Il existe deux façons de lancer le programme, en mode ligne de commande ou en mode interface graphique.

### Ligne de commande

Pour lancer le programme en mode ligne de commande, il faut taper :

```bash
php man -p runAll [-o | --output <Fichier de sortie>]
```

L'option `-o` ou `--output` permet de spécifier un fichier de sortie pour les logs. Par défaut, ils sont sur la sortie standard.

### Interface graphique

Pour lancer le programme en mode interface graphique, il faut **d'abord** lancer le serveur :

```bash
php man -p socket
```

Un socket est alors ouvert sur le port 8080. On peut désormais s'y connecter avec le client.

Il suffit d'ouvrir le fichier `client.html` dans un navigateur pour se connecter au serveur. Il n'y a pas besoin de le lancer via un serveur web, le fichier est auto-suffisant.

## Remarques diverses sur le projet

C'est un beau projet, qui a demandé et coûté beaucoup de nuits mais qui a été très enrichissant. Le code est fait pour être le plus flexible possible, mais il reste encore beaucoup d'améliorations à faire.

Tout le code est documenté et commenté pour faciliter la compréhension.

Sur chacune des méthodes, leur compléxité est indiquée.

Un diagramme de classe est disponible `diagram.puml` et en svg `diagram.svg`.

## Temps de terminaison

Notre univers se termine à l'unité de temps 14198.
