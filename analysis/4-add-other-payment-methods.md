# #4-add-other-payment-methods

Le but de cette analyse est d'ajouter d'autres moyens de paiement dans
l'application Rebels' Clubhouse pour le paiement des licences. Actuellement, les
utilisateurs ne peuvent payer leur licence que par carte bancaire via Stripe. Il
faudrait que les utilisateurs puissent également payer par :

-   virement bancaire
-   via un plan de paiement personnalisé
-   via Paypal ?

## Entités

### Création d'une entite `Payment` composée de :

-   `id`
-   `id_license`
-   `id_paymentType`
-   `is_complete` ? bool default=false

### Création d'une entité `PaymentType` composée de :

-   `id`
-   `name` string not-null

<u>Options</u> : Par carte de banque, Par virement bancaire, Par plan de
paiement  
// Prévoir traduction des PaymentType via `Gedmo\Translatable\Translatable`;

### Création d'une entité PaymentOrder composée de :

-   `id`
-   `id_payment`
-   `amount` decimal not-null
-   `due_date` date not-null
-   `value_date` date nullable
-   `comment` text nullable
-   `validated_by` = User.id nullable

## Paiement des licences

Lorsque la licence d'un membre est validée par un administrateur, elle obtient
le statut `DOC_VALIDATED ( = 4 )`. Pour obtenir le statut `IN_ORDER ( = 5 )`, la
licence doit être payée.

Sur l'écran de résumé de la license, il y a plusieurs boutons accessibles
lorsque le statut est `DOC_VALIDATED ( = 4 )` et qu'il n'existe aucun objet
`Payment` avec `id_license` = id de la license sélectionnée :

-   payer la totalité par carte
-   payer la totalité par virement bancaire
-   demander un plan de paiement

### Payer la totalité par carte de banque

Lorsque l'utilisateur choisi cette option, il est redirigé directement vers
Stripe.

-   Si le paiement réussi, on créé :

    -   un objet `Payment` avec :
        -   `id_license` = licence sélectionnée
        -   `id_paymentType` = paiement par carte de banque
        -   `is_complete` = true
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

-   Création d'un compte Stripe pour Liège Rebels Baseball & Softball Club.
-   Modification des clefs - publique et privée

### Payer la totalité par virement

Lorsque l'utilisateur choisi cette option :

-   On créé un objet `Payment` avec :
    -   `id_license` = licence sélectionnée
    -   `id_paymentType` = paiement par virement
    -   `is_complete` = false
-   On créé un objet `PaymentOrder` avec :
    -   `id_payment` = Id de l'objet `Payment` créé ci-dessus
    -   `amount` = `license.price`
    -   `due_date` = J + 1 mois
    -   `value_date` = NULL
    -   `comment` = NULL
    -   `validated_by` = NULL
-   Il est redirigé vers la page de résumé de la licence et à la place des boutons de paiement, on affiche :

    ```
    Effectuer le virement comme suit :

    Liège Rebels Baseball & Softball Club
    IBAN : BE22 3601 0058 3447
    Communication : {NOM JOUEUR} {Prénom joueur} - COTISATION {année}
    Montant : {license.price}
    ```

### Demander un plan de paiement

## Validation des paiements

Dans le menu administrateur, un sous-item "Paiements" se trouve sous Licence - au
même niveau que "A valider". Ce lien permet d'accéder à un tableau composé de
tous les ordres de paiement.

Les administrateurs peuvent créer des `PaymentOrder` manuellement. Par exemple, lorsqu'un membre arbitre ou score un match, il peut bénéficier d'une réduction de 10€ sur sa licence (actuelle si pas encore complètement payée ou suivante). Il faut donc laisser la possibilité d'introduire cette "réduction" => ajouter un nouvel ordre avec en commentaire "Arbitrage" ou "Scoring".

### Paiements par carte en ligne

Aucune action nécessaire. L'ordre est ajouté à la liste au paiement de la
licence et le `Payment` est considéré comme complet.

### Paiements par virement

Dans ce cas, il n'y a qu'un seul ordre de paiement (`PaymentOrder`) créé. Sur la
ligne de l'ordre, l'administrateur a la possibilité de valider l'ordre (bouton
"Reçu", par exemple). A la validation, l'administrateur peut compléter la date
valeur (champ `value_date` = date de réception sur compte bancaire) et ajouter un
commentaire (champ `comment`).

Ensuite, l'objet `PaymentOrder` correspondant est modifié comme suit :

-   `value_date` = date valeur encodée par l'administrateur
-   `comment` = commentaire s'il y en a un
-   `validated_by` = Id de l'administrateur qui valide

Et, le champ `is_complete` de l objet `Payment` passe à `true` et le statut de la
licence passe à `IN_ORDER ( = 5 )`.  
Un email est envoyé au membre dont le paiement a été validé pour lui confirmer la mise en ordre de sa licence.

### Paiements via un plan
