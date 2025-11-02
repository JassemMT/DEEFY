<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\action\Action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\lists\Playlist;


class AddTrackAction extends Action
{
    public function execute(): string
    {
        AuthnProvider::requireLogin();

        if (!isset($_SESSION['playlist'])) {
            return '<p>Aucune playlist courante.</p><p><a href="?action=add-playlist">Créer une playlist</a></p>';
        }

        $res = DeefyRepository::getInstance();
        $html = 
            '<p>Ajouter une piste  à la playlist ' . $res->findPlaylistById($_SESSION['playlist'])->nom . ' — choisissez le type :</p> '
            .
            '<ul>
              <li><a href="?action=add-audio-track">Ajouter une piste audio <a></li>
              <li><a href="?action=add-podcast-track">Ajouter un podcast</a></li>
            </ul>
            <p>Ou <a href="?action=add-playlist">créer une playlist</a> d\'abord.</p>
            ';
        return $html;
    }
}