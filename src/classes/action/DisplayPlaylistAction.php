<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\render\AudioListRenderer;

class DisplayPlaylistAction extends Action
{
    public function execute(): string
    {
        AuthnProvider::requireLogin();
        $user = AuthnProvider::getSignedInUser();
        $repo = DeefyRepository::getInstance();

        // Affichage d'une playlist précise si l'id est precisé
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            if (!Authz::checkPlaylistOwner($id)) {
                http_response_code(403);
                return '<p>Accès refusé à cette playlist.</p>';
            }
            $pl = $repo->findPlaylistById($id);
            if (!$pl) return '<p>Playlist introuvable.</p>';
            $renderer = new AudioListRenderer($pl);

            $_SESSION['playlist'] = $id;


            $html = $renderer->render() . '<br><a href="?action=add-track">Ajouter une piste à la playlist</a>';

            return $html;
        }

        // Sinon, lister les playlists accessibles
        if ((int)$user['role'] === Authz::ROLE_ADMIN) {
            $rows = $repo->findAllPlaylists(); // toutes les playlists
        } else {
            $rows = $repo->findAllPlaylistByUserId((int)$user['id'], false); // playlists du user
        }

        if (!$rows) return '<p>Aucune playlist.</p>';

        $html = '<h2>Playlists</h2><ul>';
        foreach ($rows as $r) {
            $pid = (int)$r['id'];
            $nom = htmlspecialchars($r['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= "<li><a href=\"?action=display-playlist&id={$pid}\">{$nom}</a></li>";
        }
        $html .= '</ul>';
        return $html;
    }


}