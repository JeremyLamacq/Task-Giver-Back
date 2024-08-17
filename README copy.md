# Projet Task-giver (Back) :

## Description du projet :

*TODO*

## Installation :
1) Cloner ce répo.

2) Créer un fichier `.env.local`  à la racine du projet et y mettre (variables entre accolades {} à remplir) :

        DATABASE_URL="mysql://{bdd_username}:{bdd_password}@127.0.0.1:3306/{bdd_database_name}?serverVersion=mariadb-10.3.38&charset=utf8mb4"

3) Ouvrez un terminal à la racine du projet et installer les dépendances avec la commande :

        composer install

4) Initialiser la BDD avec la commande suivante (assurez vous que la table indiqué dans le .env.local n'existe pas déjà, sinon supprimer la dans Adminer):

        php bin/console doctrine:database:create

## Mettre à jours la BDD :
Si la strucuture de données de la BDD à changer depuis votre dernière installation, vous pouvez la mettre à jours avec les étapes suivantes :

1) (Optionnel) Si vous n'avez pas de migrations dans le dossier `migrations` ou si vous les avez effacer.
Créer une migration de la structure des données en lancant la commande suivante à la base du projet 
   
        php bin/console ma:mi

2) Lancer la migration sur la BDD.

        php bin/console do:mi:mi

*Si cette commande vous renvoi une erreur, effacer le dossier `migrations` et recommencer en faisant la commande optionnelle suivi de celle-ci.*

## Générer des fausses données (Fixtures) :
Pour générer de fausses données durant la phase de test, lancer la commande suivante à la base du projet :

        php bin/console do:fi:lo

*Si cette commande vous renvoie une erreur c'est qu'on sans doute mal fait notre boulot !*

## Générer la clef SSL de l'authentificateur d'API :
        php bin/console lexik:jwt:generate-keypair

## Lancer le serveur d'API :
Pour lancer le serveur d'API, assurez vous d'avoir fait l'étape d'initialisation de la BDD avant et faite la commande suivante :

        php -S 0.0.0.0:8080 -t public
