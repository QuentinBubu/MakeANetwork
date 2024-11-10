# Utilisation du projet

## Installation minimale

Pour utiliser le projet, PHP 8.3 ou supérieur est nécessaire.

## Lancement du programme

Il existe deux façons de lancer le programme, en mode ligne de commande ou en mode interface graphique.

Avant toute chose, il faut se placer dans le répertoire `Man/src` pour lancer les commandes.

### Ligne de commande

Pour lancer le programme en mode ligne de commande, il faut taper :

```bash
php man -p runAll [-o | --output <Fichier de sortie>]
```

L'option `-o` ou `--output` permet de spécifier un fichier de sortie pour les logs. Par défaut, ils sont sur la sortie standard.

Dans ce mode, l'exécution du programme est beaucoup plus rapide.

### Interface graphique

Pour lancer le programme en mode interface graphique, il faut **d'abord** lancer le serveur :

```bash
php man -p socket
```

Un socket est alors ouvert sur le port 8080. On peut désormais s'y connecter avec le client.

Il suffit d'ouvrir le fichier `client.html` dans un navigateur pour se connecter au serveur. Il n'y a pas besoin de le lancer via un serveur web, le fichier est auto-suffisant.

#### Utilisation de l'interface graphique

##### Démarrage

Lorsque nous sommes conncectés au serveur, si nous sommes le premier client, une popup `Configuration` apparaît.
Les données par défaut sont alors chargées, et nous devons régler ces dernières comme bon nous semble.

Une fois fait, nous pouvons cliquer sur `Envoyer la configuration` pour envoyer les données au serveur.

A partir de ce moment là, nous pouvons démarrer la simulation en cliquant sur `Play`.

##### Boutons de contrôle

Nous avons à notre disposition plusieurs boutons de contrôle :
- `Play` : Permet de démarrer / reprendre la simulation
- `Pause` : Permet de mettre en pause la simulation
- `Forward` : Permet d'avancer d'un pas dans la simulation (utilisable uniquement en pause)
- `Backward` : Permet de reculer d'un pas dans la simulation (utilisable uniquement en pause)
- `Voir la tick` / touche `Entrée` : Permet de voir la tick sélectionnée dans le champ de texte (utilisable uniquement en pause)
- `Exporter` : Permet d'exporter les données de la simulation dans un fichier JSON et de le télécharger
- `Importer` : Permet d'importer des données de simulation depuis un fichier JSON, attention, nous ne pouvons pas continuer la simulation après l'importation