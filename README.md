# Rebels' Clubhouse

Application web pour la gestion d'un club de baseball

Projet réalisé dans le cadre de l'épreuve intégrée du Brevet d'Enseignement Supérieur de l'Institut Saint-Laurent  
Année scolaire 2023 - 2024

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- [PHP](https://www.php.net/) (version 8.1 ou supérieure)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Git](https://git-scm.com/)
- [MySQL](https://dev.mysql.com/downloads/mysql/)
- [MailHog](https://github.com/mailhog/MailHog) ou un serveur SMTP
- [Node.js](https://nodejs.org/) (pour `npm`)

## Installation en local sur Linux

1. Clonez le repertoire  
   Clonez ce dépôt sur votre machine locale en utilisant la commande suivante :

```bash
  git clone git@github.com:ngihoul/rebels2024v2.git
  git clone https://github.com/ngihoul/rebels2024v2.git
```

2. Installez les dépendances  
   Utilisez Composer pour installer les dépendances nécessaires :

```bash
  composer install
```

3. Configuration de l'environnement

- Générez une clé APP_SECRET :  
  Utilisez la commande suivante pour générer une clé secrète :

```bash
php -r 'echo bin2hex(random_bytes(16));'
```

- Ajoutez cette clé à votre fichier .env :

```bash
APP_SECRET=change_me
```

- Configurez votre base de données MySQL :  
  Ajoutez les informations de connexion à votre base de données MySQL dans votre fichier .env :

```bash
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

- Configurez votre serveur SMTP :  
  Pour l'envoi de mails, configurez votre serveur SMTP dans votre fichier .env :

```bash
MAILER_DSN=smtp://localhost:1025
```

- Configurez vos clés Stripe :  
  Ajoutez vos clés Stripe dans votre fichier .env :

```bash
STRIPE_PUBLIC_KEY=change_me
STRIPE_SECRET_KEY=change_me
```

4. Créez la base de données et exécutez les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Importez les données de base
   Ou importer les fichiers dans l'odre via [phpMyAdmin](http://localhost/phpmyadmin)

```bash
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/country.sql
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/event_category.sql
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/ext_translation.sql
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/license_category.sql
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/place.sql
mysql -u votre_utilisateur -p votre_base_de_donnees < /chemin/vers/votre_projet/db/user.sql
```

6. Installez les assets

```bash
php bin/console assets:install
npm install
npm run dev
```

## Execution

1. Démarrez le serveur web
   Pour démarrer le serveur web Symfony, utilisez la commande suivante :

```bash
symfony serve
```

Votre application sera accessible à l'adresse http://localhost:8000.

2. Accéder à l'application  
   Utilisez les identifiants et mots de passe fournis dans l'email reçu.

# Rebels' Clubhouse en production

L'application Rebels' Clubhouse est accessible en production via ce lien https://clubhouse.liegebaseball.be

---

Auteur : Nicolas Gihoul
