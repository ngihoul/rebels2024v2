# #3-parentchild-management

## Entités

### Création d'une nouvelle entité "Relation" composée de :

-   id_parent = User.id
-   id_enfant = User.id
-   id_relationType = relationType.id

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

## Connexion - LoginController

-   Connexion impossible si l utilisateur n'a pas de mot de passe => bad
    credentials
-   Stockage de l'ID de l'utilisateur effectif (= compte sélectionné) dans la
    session sous activeUserId. L'utilisateur se connecte TOUJOURS avec son
    compte principal (= parent).

## Déconnexion - LogoutController

-   Supprimer le ActiveUserId de la session !

## Navigation - base.html.twig

### Généralités

-   Lorsque l'utilisateur change de profil pour utiliser le compte de l'un de
    ses enfants, le ActiveUserId dans la session est modifié.
-   Si l'utilisateur principal (= parent) est administrateur ou coach, il ne
    doit pas avoir accès aux parties réservées à son type de rôle lorsqu'il
    utilise le compte de son enfant.
-   S'il y a un évènement en attente de réponse <b>ou</b> un message non lu dans
    la messagerie <b>ou</b> un todo pour les enfants, un badge doit être visible
    à côté du chevron vers le bas (⌄) et également à coté du profil de l'enfant
    dans la liste dropdown sur desktop ou dans le menu mobile (cf. navigation).
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

## Clubhouse

-   Le parent peut voir sur sa page d'accueil qu'il y a des opérations en
    attente chez ses enfants. Un boutton lui permet de directement switcher de
    compte et d'accéder à la bonne section (exemple : un enfant est invité à un
    entrainement, un message : "Jason a été invité à un évènement" apparait sur
    le clubhouse du parent avec un lien "Accéder aux évènement de Jason".)

## Profil

-   Dans son profil, l'utilisateur principal (= parent) peut voir la liste de
    ses enfants (prenom, nom, date de naissance et miniature de la photo de
    profil)
-   En cliquant sur un élément de la liste, il accède au profil de son enfant
    sans pour autant changer d'utilisateur.
-   Un bouton "Ajouter un enfant" est accessible par TOUS les membres ce qui
    leur permet à tout moment d'ajouter un enfant à son profil

## Cron job

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

## Avis de ChatGPT

### Gestion des Relations multiples :

        Précision sur les permissions des relations multiples : Si un enfant a plusieurs représentants légaux ou tuteurs, il serait utile de définir quel parent peut donner des permissions pour l'usage de l'application. Est-ce que les deux parents ont les mêmes droits ? Ou un seul peut gérer cela ?
        Envisage d’ajouter des permissions ou un système de priorités pour gérer des cas où plusieurs parents ou tuteurs peuvent vouloir administrer le compte de l’enfant.

### Utilisation de l’application à partir de 16 ans :

        Gestion du consentement pour les 16 ans : Que se passe-t-il si un enfant de 16 ans ne répond pas à l'email d'activation de compte ? Proposer une relance automatique via un cron job, par exemple.
        Il serait utile d’ajouter des règles ou des notifications automatiques au parent si l’enfant n’a pas activé son compte après un certain temps.

### Sécurité et ActiveUserId :

        L'utilisation de ActiveUserId pour suivre quel compte est actif est pratique, mais assure-toi d'inclure une vérification stricte dans chaque contrôleur pour garantir que les parents ne peuvent pas accéder aux sections sensibles (comme l’administration) lorsqu’ils utilisent le compte de leur enfant. Cela nécessite une vigilance supplémentaire sur les autorisations (isGranted).
        Pour renforcer la sécurité, envisage un timeout pour les sessions plus courtes si un parent utilise le compte d'un enfant, par exemple. Cela réduit le risque qu'un parent laisse son compte ouvert sous celui de l'enfant par inadvertance.

### Notifications et permissions :

        Prévois d'implémenter un système de notifications plus détaillé pour les parents. Par exemple, quand un enfant reçoit une invitation à un événement ou à un entraînement, le parent peut choisir de recevoir ces alertes par e-mail ou SMS en plus des notifications dans l'application.
        Gestion des permissions à 16 ans : Quand un enfant atteint 16 ans et obtient un accès à l'application, peut-être que les parents devraient encore avoir un contrôle partiel (au moins pour des informations critiques, comme les événements ou les changements dans les données).

### UX pour basculement de comptes (Parent/Enfant) :

        Bien que le menu déroulant soit clair pour basculer entre les comptes, il pourrait être utile d’ajouter une page de confirmation ou une alerte lors du changement de compte pour rappeler au parent qu’il agit maintenant sous un autre compte, afin d'éviter les erreurs potentielles.

### Scalabilité de l'enregistrement des logs :

        Si tu envisages de suivre les actions des parents lorsqu'ils agissent pour le compte de leurs enfants (pour des raisons légales ou de transparence), il serait peut-être judicieux de réfléchir à un système de log externe ou à un système d'archivage. Les logs peuvent devenir volumineux avec le temps et affecter les performances de ta base de données. Une alternative pourrait être d'utiliser un système comme ElasticSearch ou Logstash pour gérer et analyser ces logs de manière plus efficace.

### Gestion des utilisateurs (Parent/Admin/Coach) :

        Assure-toi que lorsqu'un parent est également un administrateur ou un coach, les droits d'accès aux fonctionnalités de ces rôles sont bien désactivés lorsqu'il agit sous le compte de son enfant. Tu l’as mentionné, mais cela demandera une vérification rigoureuse dans les contrôleurs et services.
        Envisage un message explicatif lors du basculement entre les rôles pour clarifier que certaines fonctionnalités ne sont plus accessibles en tant que représentant de l'enfant.

## Suggestions supplémentaires :

### Intégration de tutoriels ou guides :

        Étant donné la complexité des fonctionnalités (en particulier pour les nouveaux utilisateurs), tu pourrais envisager d'intégrer des tutoriels interactifs ou des guides pour montrer comment gérer les comptes enfants, changer d'utilisateur, ou donner accès à un enfant de plus de 16 ans.

### Mobile-First Design :

        Étant donné l'utilisation probable sur mobile par de nombreux parents, assure-toi que toutes les fonctionnalités, y compris le basculement de compte et la gestion des enfants, soient mobile-friendly. Un menu bien optimisé et des boutons de navigation clairs sont essentiels pour éviter toute confusion.

1. Personnalisation des Notifications

    Système de préférences de notifications : Permettre aux parents de
    configurer les types de notifications qu'ils souhaitent recevoir pour
    eux-mêmes et pour chaque enfant. Par exemple : Recevoir des notifications
    par email ou SMS pour des événements comme les matchs, entraînements,
    messages d'entraîneur, etc. Activer des rappels automatisés pour les
    événements de leurs enfants (par exemple, une notification la veille d’un
    match). Pour les enfants de 16 ans et plus, permettre aussi à l’enfant de
    définir ses propres préférences.

L'idée est de donner plus de contrôle aux utilisateurs sur les informations
qu'ils souhaitent recevoir, réduisant ainsi la surcharge de notifications non
pertinentes. 2. Meilleure Visualisation des Comptes Associés

    Dashboard visuel pour les parents : Au lieu d'une simple liste des enfants associés dans un menu déroulant, envisage un tableau de bord centralisé où les parents peuvent voir tous leurs enfants et leur statut en un coup d'œil. Ce dashboard pourrait inclure :
        Photo de l'enfant
        Statut des événements (matchs à venir, entraînements)
        Messages non lus
        Tâches ou actions urgentes (par exemple, informations manquantes pour l’inscription)

Ce tableau de bord permettrait aux parents de gérer plus efficacement plusieurs
comptes sans avoir à basculer constamment entre les profils. 3. Feedback visuel
sur le changement de compte

    Ajouter un feedback visuel plus fort lors du changement de compte, comme :
        Un changement de couleur dans la barre de navigation ou autour de l’avatar pour bien indiquer que l’utilisateur utilise un autre profil.
        Un encart fixe ou une bannière en haut de la page indiquant clairement "Vous agissez en tant que [nom de l'enfant]". Ce feedback visuel peut réduire les risques d’erreurs de navigation ou d’action sous le mauvais compte.

4. Multi-comptes pour un même enfant

    Autoriser plusieurs comptes parents pour un enfant : Dans certains cas, un
    enfant peut avoir plusieurs tuteurs ou représentants légaux (parents
    divorcés, par exemple). Il pourrait être intéressant de permettre aux deux
    parents de gérer le même compte enfant tout en ayant des profils séparés
    pour chaque parent. Il serait aussi utile de prévoir un système de
    permissions par parent, permettant à l'un des parents d'avoir plus de droits
    (par exemple, pour donner l’autorisation d’utiliser l’application seul à
    partir de 16 ans).

5. Amélioration du Cron Job

    Notifications pour le parent après 18 ans : Une fois que l'enfant atteint 18
    ans, tu as déjà prévu d'envoyer un email à l'enfant pour l’informer qu’il
    doit gérer son compte. En plus de cela, il serait judicieux d’envoyer un
    email de notification au parent pour l’informer de ce changement. Ajoute des
    vérifications régulières pour des événements similaires, comme l’arrivée à
    la date d’expiration de la licence d’un enfant ou un manque de documents.

6. Amélioration de l’expérience mobile

    Sur mobile, il serait intéressant de rendre le changement de compte encore
    plus fluide, par exemple via un geste de swipe ou un menu flottant
    permettant de basculer entre les comptes sans devoir passer par le menu
    complet. Sur des écrans plus petits, la navigation doit rester rapide et
    intuitive.

7. Meilleure gestion des autorisations après 16 ans

    Pour les enfants à partir de 16 ans, lorsque le parent donne l'autorisation
    d'utiliser l'application, une interface de contrôle parental pourrait être
    ajoutée. Cette interface permettrait aux parents de : Choisir quelles
    sections de l’application l’enfant peut gérer seul. Avoir un droit de veto
    sur certaines actions (par exemple, la participation à un événement).
    Recevoir des notifications spécifiquement liées aux actions que l’enfant a
    prises dans l’application (comme une signature de licence ou une réponse à
    un événement).

8. Suivi d’activité plus granulaire

    Améliore le suivi d’activité des parents qui agissent au nom de leurs
    enfants en stockant des informations plus granuleuses. Par exemple : Quelle
    action spécifique a été réalisée (participation à un événement, modification
    de profil, etc.) Date et heure exactes de l’action. IP de connexion (utile
    en cas de litige ou d'abus).

Tu pourrais envisager une solution hybride pour le stockage des logs : stocker
uniquement les actions critiques dans la base de données (pour permettre un
audit rapide), et les autres actions mineures dans un système de log externe
(comme ElasticSearch). 9. Gérer les permissions temporaires

    Tu pourrais aussi envisager un système de permissions temporaires. Par exemple, un parent peut autoriser temporairement son enfant à accéder à certaines parties de l'application pendant une période donnée (par exemple, pendant une compétition ou une saison).

10. Faciliter l’ajout d’enfants à posteriori

    Actuellement, tu proposes un formulaire d’ajout d’enfant lors de
    l’inscription, mais il serait aussi intéressant de permettre d’ajouter un
    enfant à tout moment via une option bien visible dans le profil parent.
    Simplification pour l’ajout d’enfants : Pour les parents ayant déjà des
    enfants inscrits, tu pourrais pré-remplir certains champs (comme l'adresse,
    le numéro de téléphone) pour faciliter l'ajout d’un nouvel enfant.
