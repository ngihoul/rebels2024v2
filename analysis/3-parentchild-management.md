# #3-parentchild-management

## Entités

### Création d'une nouvelle entité "Relation" composée de :

-   id_parent = User.id
-   id_enfant = User.id
-   id_relationType = relationType.id

// Un enfant peut avoir plusieurs parents ou représentants légaux
// Un parent peut avoir plusieurs enfants

### Création d'une nouvelle entité "RelationType" composée de :

-   id
-   name

<u>Options</u> : Parent, Représentant légal ou Frère/soeur (>= 18 ans)

// Prévoir traduction des RelationType via Gedmo\Translatable\Translatable;

### Mise à jour de l'entité User

-   Ajout d'un champ parents - relation ManyToOne avec Relation
-   Ajout d'un champ children - relation ManyToOne avec Relation
-   Ajout d'un champ canUseApp bool : seulement enfant de >= 16 ans avec
    autorisation d'un des parents
-   Ajout d'un champ canUseAppAuthorizer = User.id du parent qui donne
    l'autorisation
-   Ajout d'un champ canUseAppFromDate = DateTime de l autorisation

## DB

-   Enregistrement des enfants sans compte sans mot de passe en base de donnnées

## Inscription - RegistrationController

### Processus UX

1. Ajout d'un formulaire "UserChoiceType"

    - L'utilisateur choisi s'il veut s'inscrire en tant que joueur OU parent
    - Disclaimer indiquant qu'un parent peut bien sûr devenir un joueur

2. Utilisation de RegistrationFormType pour les données de l'utilisateur (joueur
   adulte OU parent)

    - Optionnel : cacher les champ jersey_number et license_number pour les
      parents

3. Formulaire uniquement pour les parents :

    - Formulaire dynamique permettant d'encoder de 1 à N enfants
    - Formulaire composé des champs suivants :
        - Prénom
        - Nom
        - Nationalité
        - Numéro de licence
        - Numéro de vareuse
        - Date de naissance
        - Genre
        - Un bouton même adresse que le parent OU les champs "adresse"
        - Téléphone
        - Mobile
        - Adresse mail
        - Photo de profil (facultative à l'enregistrement mais obligatoire pour
          toute demande de licence)
        - Si enfant >= 16 ans : autorisation d'utiliser l'app seul

4. Si autorisation d'utilisation de l'app seul :
    - Le jeune reçoit un mail avec un lien qui lui permet de choisir son propre
      mot de passe
    - Dès que l'autorisation est accordée, elle ne peut plus être retirée.

## Connexion - LoginController

-   Connexion impossible si l utilisateur n'a pas de mot de passe => bad
    credentials
-   Stockage de l'ID de l'utilisateur effectif (= compte sélectionné) dans la
    session sous activeUserId. L'utilisateur se connecte TOUJOURS avec son
    compte principal (= parent).

## Déconnexion - LogoutController

-   Supprimer le ActiveUserId de la session !

## Securité

-   Lorsque l'utilisateur change de profil pour utiliser le compte de l'un de
    ses enfants, le ActiveUserId dans la session est modifié.
    Il faudra donc ne plus se baser sur $this->getUser() pour vérifier quel 
    utilisateur est connecté dans nos controllers.
    Il sera nécessaire de créer un <b>service</b> permettant d'utiliser getActiveUser().
-   Si l'utilisateur principal (= parent) est administrateur ou coach, il ne
    doit pas avoir accès aux parties réservées à son type de rôle lorsqu'il
    utilise le compte de son enfant.
-   Pour augmenter la securité, on diminuera la durée de vie des sessions :
    ```
    // config/packages/framework.yaml
    framework:
        session:
            cookie_lifetime: 3600
    ```

## Navigation - base.html.twig

### Généralités

-   S'il y a un évènement en attente de réponse <b>ou</b> un message non lu dans
    la messagerie <b>ou</b> un todo pour les enfants, un badge doit être visible
    à côté du chevron vers le bas (⌄) et également à coté du profil de l'enfant
    dans la liste dropdown sur desktop ou dans le menu mobile (cf. navigation).
-   Au moment du changement de compte, une modal apparait et demande la 
    confirmation du changement de compte (cf. modal de confirmation de suppression).
    Ce message expliquera aux utilisateurs ayant un role COACH et/ou ADMIN qu'ils 
    n'auront plus accès à leurs privilèges : il sera necessaire de rebasculer sur 
    leur profil pour récupérer ses privilèges.
-   Après le changement de compte une alert-warning indiuant que l utilisateur
    utilise le compte de Jason ou Brandon reste visible tout le long de la
    navigation en dessous du h1 de la page et au dessus des messages flashes.


### Desktop

-   Modification de la nav bar
    -   Supprimer "Se déconnecter"
    -   Ajouter un toggle à droite du nom de l'utilisateur : ⌄
    -   Au clique sur : photo, nom ou chevron vers le bas (⌄) :
        -   Ouvertue d'un menu dropdown avec :
            -   la liste des utilisateurs possibles (= enfants de l'utilisateur)
                = photo, nom complet
            -   un lien "voir mon profil"
            -   un lien "se déconnecter"

### Mobile

-   Modification de la nav bar :
    -   Ajouter un toggle à droite de la photo de l'utilisateur : ⌄
    -   Au clique sur : photo ou chevron vers le bas (⌄) :
        -   Ouvertue d'un menu bleu (même design que le menu principal) venant
            de la droite comprenant :
            -   Un titre : changer de compte
            -   la liste des utilisateurs possibles (= enfants de l'utilisateur)
                : photo, nom complet
            -   Un espace conséquent
            -   un lien "voir mon profil"
            -   un lien "se déconnecter"
-   Ajout d'un bouton rond (fond bleu, comme les bouton ajout, modifier, supprimer)
    qui afficherait la liste des autres comptes possibles à utiliser.

## Clubhouse

-   Le parent peut voir sur sa page d'accueil qu'il y a des opérations en
    attente chez ses enfants. Un boutton lui permet de directement switcher de
    compte et d'accéder à la bonne section (exemple : un enfant est invité à un
    entrainement, un message : "Jason a été invité à un évènement" apparait sur
    le clubhouse du parent avec un lien "Accéder aux évènement de Jason".)

## Profil

-   Dans son profil, l'utilisateur principal (= parent) peut voir la liste de
    ses enfants (prenom, nom, date de naissance et miniature de la photo de
    profil) avec un badge si une action est requise.
-   En cliquant sur un élément de la liste, il accède au profil de son enfant
    sans pour autant changer d'utilisateur.
-   Un bouton "Ajouter un enfant" est accessible par TOUS les membres ce qui
    leur permet à tout moment d'ajouter un enfant à son profil

## Roles

-   Si l'enfant a plusieurs parents, tous les parents ont les mêmes "pouvoirs" 
    càd qu'ils peuvent tous les deux accepter des évènements, lire les messages, 
    demander une licence ou donner l'autorisation d'utiliser l'app à leur enfant
    de >= 16 ans.

## Cron job

-   Un cron job sera effectué tous les mois verifiant si des jeunes ont 
    canUseAppFromDate > 1 mois mais n'ont toujours pas de mot de passe. 
    Dans ce cas, les parents recevront un message via l'app Rebels Clubhouse 
    leur proposant d'envoyer un rappel par mail à leur enfant.
-   Un cron job sera effectué toutes les semaines le dimanche à 01:00 (pourquoi
    pas tous les jours ?) pour vérifier si des membres ont eu leur 18e
    anniversaire pendant la semaine précédente. Si c'est le cas, la liste des
    parents est vidée (s'assurer que la liste children chez les parents est mise
    à jour).  
    Si le jeune n'a pas encore de mot de passe, un mail lui est envoyé pour
    l'informer qu'il doit maintenant gérer son compte seul étant donné qu'il est
    majeur.  
    Pour ce faire, il pourra choisir un mot de passe et devra accepter les : -
    Newsletter - ROI - Privacy Policy
    Un mail sera également envoyé à ses parents afin de les informer que le jeune
    doit maintenant gérer son compte seul.
-   Un cron job sera effectué tous les mois pour vérifier si des membres 
    de > 18 ans n'ont pas de mot de passe et leur anniversaire il y a > 1 mois.
    Dans ce cas, ils recevront un rappel pour choisir un mot de passe et utiliser
    leur compte seul.

## Idées optionnelles (Merci ChatGPT)

### Enregistrement des logs :

-   Si tu envisages de suivre les actions des parents lorsqu'ils agissent pour le 
    compte de leurs enfants (pour des raisons légales ou de transparence), il 
    serait peut-être judicieux de réfléchir à un système de log externe ou à un 
    système d'archivage. Les logs peuvent devenir volumineux avec le temps et 
    affecter les performances de ta base de données. Une alternative pourrait être
    d'utiliser un système comme ElasticSearch ou Logstash pour gérer et analyser 
    ces logs de manière plus efficace.

### Intégration de tutoriels ou guides :

-   Étant donné la complexité des fonctionnalités (en particulier pour les 
    nouveaux utilisateurs), tu pourrais envisager d'intégrer des tutoriels 
    interactifs ou des guides pour montrer comment gérer les comptes enfants, 
    changer d'utilisateur, ou donner accès à un enfant de plus de 16 ans. 

### Suivi d’activité plus granulaire

-   Améliore le suivi d’activité des parents qui agissent au nom de leurs
    enfants en stockant des informations plus granuleuses. Par exemple : Quelle
    action spécifique a été réalisée (participation à un événement, modification
    de profil, etc.) Date et heure exactes de l’action. ID du parent ayant réalisé
    l'opération.

-   Tu pourrais envisager une solution hybride pour le stockage des logs : stocker
    uniquement les actions critiques dans la base de données (pour permettre un
    audit rapide), et les autres actions mineures dans un système de log externe
    (comme ElasticSearch). 
