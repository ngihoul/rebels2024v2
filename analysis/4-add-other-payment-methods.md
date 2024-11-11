# [#4-add-other-payment-methods](https://github.com/ngihoul/rebels2024v2/issues/4)

Le but de cette analyse est d'ajouter d'autres moyens de paiement dans
l'application Rebels' Clubhouse pour le paiement des licences. Actuellement, les
utilisateurs ne peuvent payer leur licence que par carte bancaire via Stripe. Il
faudrait que les utilisateurs puissent également payer par :

-   virement bancaire
-   via un plan de paiement personnalisé

## Entités

### Création d'une entite `Payment` composée de :

-   `id`
-   `id_license`
-   `payment_type` // by_Stripe (1), by_bank_transfer (2) & by_payment_plan (3)
-   `status` // Accepted (1), refused (2) & completed (3) nullable
-   `user_comment` text nullable
-   `refusal_comment` text nullable
-   `created_at` DateTime not null - Géré automatiquement par
    `Gedmo\Timestampable`
-   `updated_at` DateTime nullable - Géré automatiquement par
    `Gedmo\Timestampable`

### Création d'une entité `PaymentOrder` composée de :

-   `id`
-   `id_payment`
-   `amount` decimal not-null
-   `due_date` date not-null
-   `value_date` date nullable
-   `comment` text nullable
-   `validated_by` = User.id nullable

Le champ `due_date` sera toujours une fin de mois.

## Paiement des licences

Lorsque la licence d'un membre est validée par un administrateur, elle obtient
le statut `DOC_VALIDATED ( = 4 )`.  
Pour obtenir le statut `IN_ORDER ( = 5 )`, la licence doit être payée.

Sur l'écran de résumé de la license, il y a plusieurs boutons accessibles
lorsque le statut est `DOC_VALIDATED ( = 4 )` et qu'il n'existe aucun objet
`Payment` avec `id_license` = id de la license sélectionnée OU qu'il existe
uniquement des objet `Payment` avec le statut `Refused (= 2)` :

-   payer la totalité en ligne par carte de banque ou Paypal
-   payer la totalité par virement bancaire
-   demander un plan de paiement

### Payer la totalité en ligne par carte de banque ou Paypal

Lorsque l'utilisateur choisi cette option, il est redirigé directement vers
Stripe.

-   Si le paiement réussi, on créé :

    -   un objet `Payment` avec :

        -   `id_license` = id licence sélectionnée
        -   `payment_type` = `BY_STRIPE ( = 1)`
        -   `status` = `COMPLETED ( = 3 )`

    -   un objet `PaymentOrder` avec :
        -   `id_payment` = Objet `Payment` créé ci-dessus
        -   `amount` = `license.price`
        -   `due_date` = date de l'opération
        -   `value_date` = date de l'opération
        -   `comment` = NULL
        -   `validated_by` = NULL

    Ensuite, le statut de la licence passe en `IN_ORDER ( = 5 )` et
    l'utilisateur est redirigé vers la page de résume de la licence.

-   Si le paiement échoue, rien n'est créé et l'utilisateur est redirigé vers la
    page de licence lui permettant de réessayer de payer.

➡ Il reste à mettre en place les actions ci-dessous :

-   Création d'un compte Stripe pour Liège Rebels Baseball & Softball Club (Fait
    par Crad)
-   Modification des clefs - publique et privée

### Payer la totalité par virement

Lorsque l'utilisateur choisi cette option :

-   On créé un objet `Payment` avec :

    -   `id_license` = id licence sélectionnée
    -   `payment_type` = `BY_BANK_TRANSFER ( = 2 )`
    -   `status` = `ACCEPTED ( = 1 )`

-   On créé un objet `PaymentOrder` avec :

    -   `id_payment` = Id de l'objet `Payment` créé ci-dessus
    -   `amount` = `license.price`
    -   `due_date` = J + 1 mois
    -   `value_date` = NULL
    -   `comment` = NULL
    -   `validated_by` = NULL

-   Il est redirigé vers la page de résumé de la licence et à la place des
    boutons de paiement, on affiche :

    ```
    Effectue le virement comme suit :

    Liège Rebels Baseball & Softball Club
    IBAN : BE22 3601 0058 3447
    Communication : {NOM JOUEUR} {Prénom joueur} - COTISATION {année}
    Montant : {license.price}
    ```

    Un boutton `Changer de méthode de paiement` est visible. S'il est cliqué,
    l'objet `Payment` et les `PaymentOrder` référants sont supprimés. La page
    résumé est rafraichie et est de nouveau visible avec le choix de la méthode
    de paiement.

### Demander un plan de paiement

Lorsque l'utilisateur choisi cette option, il est redirigé vers un formulaire
`PaymentPlanRequestType` composé uniquement d'un <u>textarea</u>.

Dans ce formulaire, le membre peut décrire le plan de paiement qu'il souhaite.

Un disclaimer reprendra les information importante :

```
- Le premier versement doit être au minimum de 80€.
- Le plan de paiement ne peut jamais dépasser l'année en cours (aucun paiement en {année + 1}).
```

Après l'envoi du formulaire `PaymentPlanRequestType`, un objet `Payment` est
créé comme suit :

-   `id_license` = licence sélectionnée
-   `payment_type` = `BY_PAYMENT_PLAN ( = 3 )`
-   `status` = PENDING ( = NULL )
-   `user_comment` = texte entré dans le textarea

Et, un mail est envoyé aux administrateurs pour les informer qu'une demande de
plan de paiement est arrivée.

Ensuite, l'utilisateur est redirigé vers la page résumé de licence. A la place
des boutons de choix de paiement, on affiche :

```
Demande de plan de paiement en cours de validation
```

Un administrateur devra ensuite valider ou refuser le plan de paiement (cf.
[Validation des paiements > Paiements via un plan](#paiements-via-un-plan))

-   Si le plan de paiement est refusé, on affiche les boutons de choix de mode
    de paiements initiaux.

-   Si le plan de paiement est accepté, on affiche la liste des `PaymentOrder`.
    Pour chaque ordre de paiement, le membre à la possibilité de le payer par
    carte de banque directement via l'application et Stripe en cliquant sur
    `Payer par carte` à droite de l'ordre.

    Les ordres de paiements pour lesquels la `due_date` est dépassée s'affiche
    en rouge avec un logo `/!\` (cf. [Cron jobs](#cron-jobs))

Etant donné que le membre peut payer ses échéances par virement, on reprend, en
dessous de la liste des ordres, les modalités pour le virement :

```
    Effectue tes virements comme suit :

    Liège Rebels Baseball & Softball Club
    IBAN : BE22 3601 0058 3447
    Communication : {NOM JOUEUR} {Prénom joueur} - COTISATION {année}
    Montant : Montant de ton échéance

    ----

    Pense à créer un ordre permanent ;-)
```

A chaque paiement d'ordre (effectué par le membre lui-même ou validé d'un
administrateur), on vérifie s'il reste des ordre à payer.

Si toutes les échéances sont payées, le champ `status` de l'objet `Payment` est
mis à `COMPLETED ( = 3 )` et le statut de la licence est mis à
`IN_ORDER ( = 5 )`. Ensuite, on n'affiche plus rien d'autre que le résumé de la
licence.

## Validation des paiements

Dans le menu administrateur, un sous-item `Paiements` se trouve sous `Licence` -
au même niveau que `A valider`. Ce lien permet d'accéder à un tableau composé de
tous les ordres de paiement.

### Paiements par carte en ligne

Aucune action nécessaire. L'ordre est ajouté à la liste au paiement de la
licence et le `Payment` est considéré comme complet.

### Paiements par virement

Dans ce cas, il n'y a qu'un seul ordre de paiement `PaymentOrder` créé. Sur la
ligne de l'ordre, l'administrateur a la possibilité de valider l'ordre (bouton
"Valider"). A la validation, l'administrateur peut compléter la date valeur
(champ `value_date` = date de réception sur compte bancaire) et éventuellement
ajouter un commentaire (champ `comment`).

Ensuite, l'objet `PaymentOrder` correspondant est modifié comme suit :

-   `value_date` = date valeur encodée par l'administrateur
-   `comment` = commentaire s'il y en a un
-   `validated_by` = Id de l'administrateur qui valide

Et, le champ `status` de l objet `Payment` passe à `COMPLETED ( = 3 )` et le
statut de la licence passe à `IN_ORDER ( = 5 )`.  
Un email est envoyé au membre dont le paiement a été validé pour lui confirmer
la mise en ordre de sa licence.

### Paiements via un plan

Dès l'envoi d'une demande de plan de paiement par un utilisateur, un email est
envoyé aux administrateurs pour les informer qu'une demande de plan de paiement
a été envoyée.

Les administrateurs retrouvent toutes les demandes de plan de paiement avec un
`status` = NULL dans un tableau séparé des ordres de paiements sur la page
`Paiements` accessible via le menu administrateur sous `Licence`, au même niveau
que `A valider`.

Le tableau reprend les champs suivants :

-   Nom du demandeur
-   Prenom du demandeur
-   Montant de la licence

En cliquant sur la ligne, l'administrateur accède aux details de la demande
reprenant :

-   Nom du demandeur
-   Prenom du demandeur
-   Détails de la licence
-   Montant de la licence
-   Texte de demande de plan de paiement

L'administrateur peut ensuite accepter ou refuser la demande de paiement :

-   S'il refuse, il indique la raison du refus (placé dans le champ
    `refusal_comment` de l'objet `Payment`). Un message (avec mail) est envoyé
    au membre pour l'informer du refus et de la raison.  
    Le `status` de l'objet `Payment` est passé à `REFUSED ( = 2 )`.
-   S'il accepte, il est redirigé vers le formulaire `PaymentOrderType` d'ajout
    manuel de `PaymentOrder`.  
    Sur la même page, il créé autant d'ordres que demandés. Chaque ordre se
    compose :

    -   `amount` = montant de l'échéance
    -   `due_date` = fin du mois de l'échéance
    -   `value_date` = NULL
    -   `comment` = NULL
    -   `validated_by` = NULL

    Une vérification est faite (côté frontend & backend) afin que le premier
    ordre soit au minimum 80€ et que la totalité des ordres soit égale à
    `licence.price`.

Après validation, l'administrateur est redirigé vers la page des `Paiements`.  
Les ordres sont visibles dans la liste des ordres.  
La validation des ordres individuels se fait comme pour les virements (cf.
[Paiement par virement](#paiements-par-virement)).

## Cron jobs

-   Etant donné que les échéances des paiements sont toujours en fin de mois, on
    exécute un job, tous les cinqs de chaque mois, qui vérifie si des
    `PaymentOrder` sont impayés ( `value_date` = NULL). Pour tous les impayés,
    un message de rappel (avec mail) est envoyé aux membres concernés.
