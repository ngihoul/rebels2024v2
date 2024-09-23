# #3-parentchild-management

## Entités
### Création d'une nouvelle entité "Relation" composée de :  
- id_parent = User.id
- id_enfant = User.id
- id_relationType = relationType.id

### Création d'une nouvelle entité "RelationType" composée de :  
- id
- name  

<u>Options</u> : Parent, Représentant légal ou Frère/soeur (>= 18 ans)  

// Prévoir traduction des RelationType via Gedmo\Translatable\Translatable; 

### Mise à jour de l'entité User
- Ajout d'un champ parents - relation ManyToOne avec Relation
- Ajout d'un champ children - relation ManyToOne avec Relation
- Ajout d'un champ canUseApp bool : seulement enfant de >= 16 ans avec autorisation d'un des parents
- Ajout d'un champ canUseAppAuthorizer = User.id du parent qui donne l'autorisation
- Ajout d'un champ canUseAppFromDate = DateTime de l autorisation

## DB
-  Enregistrement des enfants sans compte sans mot de passe en base de donnnées

## Inscription - RegistrationController
### Processus UX

1. Ajout d'un formulaire "UserChoiceType"  
    - L'utilisateur choisi s'il veut s'inscrire en tant que joueur OU parent  
    - Disclaimer indiquant qu'un parent peut bien sûr devenir un joueur

2. Utilisation de RegistrationFormType pour les données de l'utilisateur (joueur adulte OU parent)
    - cacher les champ jersey_number et license_number pour les parents

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
        - Photo de profil
        - Si enfant >= 16 ans : autorisation d'utiliser l'app seul

4. Si autorisation d'utilisation de l'app seul :
   - Le jeune reçoit un mail avec un lien qui lui permet de choisir son propre mot de passe

## Connexion - LoginController

- Connexion impossible si l utilisateur n'a pas de mot de passe => bad credentials
- Stockage de l'ID de l'utilisateur effectif (= compte sélectionné) dans la session sous activeUserId

## Déconnexion - LogoutController

- Supprimer le ActiveUserId de la session !

## Navigation - base.html.twig

### Généralités

- Lorsque l'utilisateur change de profil pour utiliser le compte de l'un de ses enfants, le ActiveUserId dans la session est modifié.
- Si l'utilisateur principal (= parent) est administrateur ou coach, il ne doit pas avoir accès aux parties réservées à son type de rôle lorsqu'il utilise le compte de son enfant.
- S'il y a un évènement en attente de réponse <b>ou</b> un message non lu dans la messagerie <b>ou</b> un todo pour les enfants, un badge doit être visible à côté du chevron vers le bas (⌄) et également à coté du profil de l'enfant dans la liste dropdown sur desktop ou dans le menu mobile (cf. navigation).
- Après le changement de compte une alert-warning reste visible tout le long de la navigation en dessous du h1 de la page et au dessus des messages flashes.

### Desktop

- Modification de la nav bar
    - Supprimer "Se déconnecter"
    - Ajouter un toggle à droite du nom de l'utilisateur : ⌄
    - Au clique sur : photo, nom ou chevron vers le bas (⌄) :
        - Ouvertue d'un menu dropdown avec :
            - la liste des utilisateurs possibles (= enfants de l utilisateur) = photo, nom complet
            - un lien "voir mon profil"
            - un lien "se déconnecter"

### Mobile

- Modification de la nav bar :
    - Ajouter un toggle à droite de la de l'utilisateur : ⌄
    - Au clique sur : photo ou chevron vers le bas (⌄) :
        - Ouvertue d'un menu bleu (même design que le menu principal) venant de la droite comprenant :
            - Un titre : changer de compte
            - la liste des utilisateurs possibles (= enfants de l utilisateur) : photo, nom complet
            - Un espace conséquent
            - un lien "voir mon profil"
            - un lien "se déconnecter"

## Clubhouse

- Le parent peut voir sur sa page d'accueil qu'il y a des opérations en attente chez ses enfants. Un boutton lui permet de directement switcher de compte et d'accéder à la bonne section (exemple : un enfant est invité à un entrainement, un message : "Jason a été invité à un évènement" apparait sur le clubhouse du parent avec un lien "Accéder aux évènement de Jason".)

## Profil

- Dans son profil, l'utilisateur principal (= parent) peut voir la liste de ses enfants (prenom, nom, date de naissance et miniature de la photo de profil)
- En cliquant sur un élément de la liste, il accède au profil de son enfant sans pour autant changer d'utilisateur.
- Un bouton "Ajouter un enfant" est accessible par TOUS les membres ce qui leur permet à tout moment d'ajouter un enfant à son profil

## Cron job

- Un cron job sera effectué toutes les semaines le dimanche à 01:00 (pourquoi pas tous les jours ?) pour vérifier si des membres ont eu leur 18e anniversaire pendant la semaine précédente. Si c'est le cas, la liste des parents est vidée (s'assurer que la liste children chez les parents est mise à jour).  
Si le jeune n'a pas encore de mot de passe, un mail lui est envoyé pour l'informer qu'il doit maintenant gérer son compte seul étant donné qu'il est majeur.  
Pour ce faire, il pourra choisir un mot de passe et devra accepter les :
    - Newsletter
    - ROI
    - Privacy Policy


