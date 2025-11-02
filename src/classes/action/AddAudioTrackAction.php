<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\action\Action;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;

class AddAudioTrackAction extends Action
{
    public function execute(): string 
    {
        AuthnProvider::requireLogin();
        
        $html = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $rawTitle      = $_POST['title'] ?? '';
            $rawGenre      = $_POST['genre'] ?? '';
            $rawDuration   = $_POST['duration'] ?? '';
            //$rawSrc        = $_POST['src'] ?? '';
            $rawArtiste    = $_POST['artiste'] ?? '';
            $rawAlbum      = $_POST['album'] ?? '';
            $rawAnnee      = $_POST['annee'] ?? '';
            $rawNumero     = $_POST['numero_piste'] ?? '';

            $title    = trim(filter_var((string)$rawTitle, FILTER_SANITIZE_SPECIAL_CHARS));
            $genre    = trim(filter_var((string)$rawGenre, FILTER_SANITIZE_SPECIAL_CHARS));
            $duration = (int) trim(filter_var((string)$rawDuration, FILTER_SANITIZE_SPECIAL_CHARS));
            //$src      = trim(filter_var((string)$rawSrc, FILTER_SANITIZE_URL));

            $artiste      = trim(filter_var((string)$rawArtiste, FILTER_SANITIZE_SPECIAL_CHARS));
            $album        = trim(filter_var((string)$rawAlbum, FILTER_SANITIZE_SPECIAL_CHARS));
            $annee        = filter_var($rawAnnee, FILTER_VALIDATE_INT);
            $numero_piste = filter_var($rawNumero, FILTER_VALIDATE_INT);


            /*
                    title correspond à AudioTrack->titre
                    genre correspond à AudioTrack->genre
                    duration correspond à AudioTrack->duree
                    src correspond à AudioTrack->filename --Celui-ci est géré par l'upload de fichier
                    artiste correspond à AlbumTrack->artiste
                    album correspond à AlbumTrack->album
                    annee correspond à AlbumTrack->annee
                    numero_piste correspond à AlbumTrack->numero_piste


                    title et src sont dans le constructeur obligatoire d'AudioTrack
                  
                    genre et duree sont determinés avec le setter magique d'AudioTrack
                    
                    artiste, album, annee, numero_piste sont determinés avec le setter magique d'AlbumTrack

                 */


            if ($title === '') {
                return '<p>Titre invalide.</p>' . $this->formulaire_ajout_piste();
            }

            // Gestion upload fichier
            if (isset($_FILES['userfile']) && isset($_FILES['userfile']['error']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['userfile']['name'];
                $tmpName = $_FILES['userfile']['tmp_name'];
                $mimeType = $_FILES['userfile']['type'] ?? '';
                $origLower = strtolower((string)$originalName);

                $hasMp3Ext = substr($origLower, -4) === '.mp3';
                $isAudioMpeg = $mimeType === 'audio/mpeg';

                $origExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?? '');
                if ($origExt === 'php') {
                    return '<p>Type de fichier non autorisé.</p>' . $this->formulaire_ajout_piste();
                }

                if (! $hasMp3Ext || ! $isAudioMpeg) {
                    return '<p>Fichier invalide : seul le MP3 est accepté.</p>' . $this->formulaire_ajout_piste();
                }

                $projectRoot = dirname(__DIR__, 3);
                $audioDir = $projectRoot . DIRECTORY_SEPARATOR . 'audio';
                if (!is_dir($audioDir)) {
                    @mkdir($audioDir, 0755, true);
                }

                try {
                    $random = bin2hex(random_bytes(16));
                } catch (\Throwable $e) {
                    $random = uniqid('', true);
                }
                $targetFilename = $random . '.mp3';
                $targetPath = $audioDir . DIRECTORY_SEPARATOR . $targetFilename;

                if (!move_uploaded_file($tmpName, $targetPath)) {
                    return '<p>Erreur lors de l\'upload du fichier.</p>' . $this->formulaire_ajout_piste();
                }

                $src = $targetFilename;
            }

            // Si l'un des champs album/artiste/annee/numero est renseigné -> AlbumTrack
            $isAlbum = $album !== '' || $artiste !== '' || $annee !== false || $numero_piste !== false;

            if ($isAlbum) {
                // Valeurs par défaut pour constructeur AlbumTrack
                $albumForCtor = $album !== '' ? $album : 'unknown';
                $numeroForCtor = ($numero_piste !== false && $numero_piste !== null) ? (int)$numero_piste : 0;

                // Constructeur AlbumTrack: titre, filename, album, numero_piste
                $track = new AlbumTrack($title, $src, $albumForCtor, $numeroForCtor, $artiste, $annee);

                
            } else {
                // Constructeur AudioTrack: titre, filename
                $track = new AudioTrack($title, $src);
            }

            // Setter magique pour genre et durée (hérité dans AlbumTrack)
            if ($genre !== '') {
                $track->genre = $genre;
            }
            $track->duree = $duration;

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $playlist = $_SESSION['playlist'] ?? null;

            if ($playlist == null) {
                return '<p>Aucune playlist trouvée. Créez une playlist avant d\'ajouter des pistes.</p>';
            }

            $_SESSION['playlist'] = $playlist;

            $repo = DeefyRepository::getInstance();
            $this->save_database($track);
            
            $html .= (new AudioListRenderer($repo->findPlaylistById($playlist)))->render();
            $html .= '<p><a href="?action=add-track">Ajouter encore une piste</a></p>';

            

            return $html;
        }

        $html .= $this->formulaire_ajout_piste();
        return $html;
    }

    public function formulaire_ajout_piste(): string
    {
        $html = '<form method="post" enctype="multipart/form-data">
                    <label>Titre : <input type="text" name="title" required></label><br>
                    <label>Genre : <input type="text" name="genre"></label><br>
                    <label>Durée (sec) : <input type="text" name="duration"></label><br>
                    <fieldset>
                      <legend>Informations d\'album (laissez vide pour une piste simple)</legend>
                      <label>Artiste : <input type="text" name="artiste"></label><br>
                      <label>Album : <input type="text" name="album"></label><br>
                      <label>Année : <input type="number" name="annee" min="0"></label><br>
                      <label>Numéro dans l\'album : <input type="number" name="numero_piste" min="0"></label><br>
                    </fieldset>
                    <label>Fichier audio (MP3) : <input type="file" name="userfile" accept=".mp3,audio/mpeg"></label><br>
                    <button type="submit">Ajouter la piste</button>
                 </form>';


                 /*
                    title correspond à AudioTrack->titre
                    genre correspond à AudioTrack->genre
                    duration correspond à AudioTrack->duree
                    src correspond à AudioTrack->filename --Celui-ci est géré par l'upload de fichier
                    artiste correspond à AlbumTrack->artiste
                    album correspond à AlbumTrack->album
                    annee correspond à AlbumTrack->annee
                    numero_piste correspond à AlbumTrack->numero_piste
                 */

        return $html;
    }

    public function save_database(AudioTrack $track): void
    {
        $repo=DeefyRepository::getInstance();
        $repo->saveTrack($track,(string) $_SESSION['playlist']);
    }

    

}