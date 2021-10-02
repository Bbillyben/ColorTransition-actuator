# Color Transition_Actuator plugin pour Jeedom

<p align="center">
  <img width="100" src="/plugin_info/ColorTransition_actuator_icon.png">
</p>

Ce plugin permet de dérouler des transitions de couleurs à partir du plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a> sur des commandes.

Les couleurs sont applicables par la méthode `event` d'une commande info, par des commandes de type message (dans le titre ou le corps du message) ou des commandes type color. 
Le plugin permet également d'appeler un scénario avec le tag color. 

Vous pouvez configurer plusieurs équipements/commandes, de différents types, à mettre à jour dans un seul équipement ColorTransform_actuator pour synchroniser les transitions.

**Usage**, Ce plugin permet de:
1. Définir une valeur du curseur pour mettre à jour les 'actionneurs' par le calcul de la couleur par le plugin ColorTransform
2. Lancer une transition automatique selon un timing défini


## Equipement 

> !! la configuration de l'équipement enverra une erreur si vous n'avez pas installé le plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>

 ### Paramètres généraux      
 <p align="center">
  <img width="50%%" src="/plugin_info/img/equipement.png">
</p>

 * __Nom de l'équipement__ 
 * __Objet parent__ 
 * __Catégorie__ 
 Comme tout équipement classique

 ### Equipement ColorTransition  
 
 il s'agit d'un équipement du plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>
 
 La liste des équipements disponibles présente les équipements colortransform activés.
 
 ### Paramètres Transition
 
 * __Durée Move In__ : durée, en secondes, de la transition vers le haut (100%) ou vers la cible
 * __Durée Move Out__ : durée, en secondes, de la transition vers le bas (0%) ou en partant de la cible vers la valeur courante
 * __Intervalle de mise à jour__ : Intervalle, en secondes, de mise à jour de la transition. Ne descendez pas trop bas (<0.5) au risque de ralentir la transition, voir le système!

## Commandes

 Neuf commandes sont crées avec l'équipement : 
 * __Curseur__ : Info type numeric qui contient la valeur du curseur. Les bornes min max sont renseignées à partir des bornes définies dans l'équipement ColorTransition choisi
* __Set Curseur__ : Action type slider qui permet de définir la valeur de *Curseur* entre les bornes spécifiées
 * __Cible Curseur__ : Info type numeric qui contient la valeur cible du curseur. cette valeur est supprimée en fin de transition
* __Set Cible__ : Action type slider qui permet de définir la valeur de *Cible Curseur*
* __Couleur Courante__ : Info type String qui contient la valeur actuellement calculée pour la transition

* __Status__ : Info type binaire qui renseigne si la transition est en cours de mouvement (1/true : en cours, 0/false : arrêtée)

* __Move In__ : Action qui permet de lancer la transition : 
  * vers le haut/100% ou 
  * vers la valeur de `cible curseur` si elle est définie
* __Move Out__ : Action qui permet de lancer la transition : 
  * vers le bas/0% ou 
  * *A partir de* la valeur de `cible curseur` si elle est définie vers la valeur actuelle de `Curseur`
* __Stop__ : Action qui permet d'arrêter la transition

> *Note :* les templates par défaut appliqués à `Couleur Courante`, `Set Curseur` et `Set Index` sont ceux du plugin ColorTransform

## Actionneurs

Liste des commandes ou scénarios à activer lors des mises à jour des transitions.
 <p align="center">
  <img width="100%" src="/plugin_info/img/actionneur.png">
</p>

* __Nom__ : un nom unique que vous choisissez

**Paramètre de la commande :**

* __Type__ : le type de commande à appeler : 
   * *Commande action* : pour une commande de type Info ou Action
   * *Scenario* : pour appeler un scénario 
* __Commande__ : permet de sélectionner une commande ou un scenario selon le __Type__ défini
* __Destination__ : Permet de définir comment sera transmise la couleur via la commande, dépends de ce qui a été sélectionné précédemment.
  * *event de la commande info* : Si la commande est de type info
  * *tags du scenario* : Si la commande est un scénario. Le tag `#color#` sera renseigné avec le démarrage du scénario
  * *Titre* : Si la commande est une commande Action de type message, la couleur sera envoyée via le titre
  * *Message* : Si la commande est une commande Action de type message, la couleur sera envoyée via le corps du message
  * *Color* : Si la commande est une commande Action de type color

**Paramètre de la couleur :**
se réfère aux paramètres de format de l'équipement ColorTransform (voir [Doc](https://github.com/Bbillyben/ColorTransition/blob/master/README.md#sortie-couleurdoc))
* __Utiliser le canal Alpha__ : Si coché, le canal alpha sera ajouté
* __Utiliser le canal Blanc__ : Si coché, le canal blanc sera ajouté
* __Format de la sortie__ : spécifie le format de la sortie 
  * *Hexadécimal* : format ``#AAWWRRGGBB`` ou ``#AARRGGBB`` ou ``#WWRRGGBB`` ou ``#RRGGBB``  
  * *json* : format type json : ``{"r":rr,"g":gg,"b":bb,"a":aa, "w":ww}``, avec ou sans les canaux ``a`` et ``w``, au minimal ``{"r":rr,"g":gg,"b":bb}``

