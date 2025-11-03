<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;
class AddPlaylistAction extends Action
{
    public function execute(): string
    {
        \iutnc\deefy\auth\AuthnProvider::requireLogin();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($name === '') return '<p>Nom invalide.</p>' . $this->formulaire_ajout_playlist();

            $pl = new \iutnc\deefy\audio\lists\Playlist($name);

            $repo = \iutnc\deefy\repository\DeefyRepository::getInstance();
            $uid  = $_SESSION['user']['id'] ?? null;
            if ($uid === null) return '<p>Non connecté.</p>';

            $playlistId = $repo->savePlaylist($pl, (int)$uid);

            

            return '<p>Playlist créée.</p><p><a href="?action=add-track">Ajouter une piste</a></p>';
        }

        return $this->formulaire_ajout_playlist();
    }

    public function formulaire_ajout_playlist(): string
    {
        return '<form method="post"><label>Nom: <input name="name" required></label><button type="submit">Créer</button></form>';
    }
}
