# DEEFY

DEEFY est une application web PHP simple pour gérer des playlists et des pistes. Ce dépôt GitHub contient le code source ; l'application est déployée et utilisable à :
https://webetu.iutnc.univ-lorraine.fr/www/e52526u/DEEFY/

## À propos
Application PHP légère pour créer, modifier et consulter des playlists. Conçue pour être facile à installer et à étendre.

## Fonctionnalités (menu d’accueil)
Les fonctionnalités suivantes sont accessibles depuis le menu d’accueil :
1. Mes playlists : affiche la liste des playlists de l’utilisateur authentifié.
2. Chaque élément de la liste est cliquable et affiche la playlist qui devient la playlist courante stockée en session.
3. Ajouter une piste : depuis l’affichage d’une playlist, ouvrir un formulaire pour ajouter une nouvelle piste à la playlist courante.
4. Créer une playlist vide : formulaire pour saisir le nom d’une nouvelle playlist ; à la validation la playlist est créée en base et devient la playlist courante.
5. Afficher la playlist courante : affiche la playlist stockée en session.
6. S’inscrire : créer un compte utilisateur avec le rôle STANDARD.
7. S’authentifier : fournir ses credentials pour s’authentifier en tant qu’utilisateur enregistré.

## Utilisation
1. S’inscrire puis s’authentifier.
2. Aller au menu d’accueil pour gérer les playlists et pistes selon les fonctionnalités listées.
3. La playlist « courante » est conservée en session pour ajout et affichage rapide.
