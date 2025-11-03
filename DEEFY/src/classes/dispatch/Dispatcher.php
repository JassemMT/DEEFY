<?php

namespace iutnc\deefy\dispatch;
use iutnc\deefy\action as act;

class Dispatcher
{
    private string $action;
    function __construct(string $action)
    {
        $this->action = $action;
    }

    public function run(): void
    {
        // choisir l'action
        switch ($this->action) {
            case 'add-playlist':
                $obj = new act\AddPlaylistAction();
                break;
            case 'add-track':
                $obj = new act\AddTrackAction();
                break;
            case 'add-audio-track':
                $obj = new act\AddAudioTrackAction();
                break;
            case 'add-podcast-track':
                $obj = new act\AddPodcastTrackAction();
                break;
            case 'add-user':
                $obj = new act\AddUserAction();
                break;
            case 'playlist':
                $obj = new act\DisplayPlaylistAction();
                break;
            case 'signin':
                $obj = new act\SignInAction();
                break;
            case 'logout':
                $obj = new act\LogOutAction();
                break;
            case 'display-playlist':
                $obj = new act\DisplayPlaylistAction();
                break;
        
            default:
                $obj = new act\DefaultAction();
                break;
        }

        // execute and render
        $html = $obj->execute();
        $this->renderPage($html);
    }

    private function renderPage(string $html): void
    {
        $title = 'Deefy App';
        $menu = '<nav><a href="?action=default">Accueil</a> | ';

        if(isset($_SESSION['user'])) {
            $menu .= '<a href="?action=add-track">Ajouter une piste</a> | 
            <a href="?action=playlist">Mes playlists</a> | 
            <a href="?action=add-playlist">Créer une playlist</a></nav> |
            <a href="?action=playlist&id='. $_SESSION['playlist'].'">Playlist courante</a></nav> |
            <a href="?action=logout">Déconnexion</a></nav>';
        } else{
            $menu .= '<a href="?action=signin">Connexion</a> '.
                     '| <a href="?action=add-user">Créer un compte</a></nav>';
        }

        echo <<<HTML
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:18px;}</style>
</head>
<body>
  <header><h1>{$title}</h1>{$menu}<hr></header>
  <main>{$html}</main>
</body>
</html>
HTML;
    }
}