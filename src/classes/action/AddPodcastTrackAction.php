<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\action\Action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

class AddPodcastTrackAction extends Action
{

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __construct()
    {
        $this->http_method = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->hostname = $_SERVER['HTTP_HOST'] ?? null;
        $this->script_name = $_SERVER['SCRIPT_NAME'] ?? null;
    }

    public function execute(): string
    {
        \iutnc\deefy\auth\AuthnProvider::requireLogin();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->formulaire_ajout_podcast();
        }

        $title   = trim((string)($_POST['title'] ?? ''));
        $genre   = trim((string)($_POST['genre'] ?? ''));
        $durationRaw = trim((string)($_POST['duration'] ?? '')); // peut être "mm:ss" ou secondes
        $auteur  = trim((string)($_POST['auteur'] ?? ''));
        $date    = trim((string)($_POST['date'] ?? ''));
        $playlistId = (int)($_SESSION['playlist'] ?? 0);

        if ($title === '') {
            return '<p>Titre invalide.</p>' . $this->formulaire_ajout_podcast();
        }
        if ($playlistId <= 0) {
            return '<p>Aucune playlist cible.</p>' . $this->formulaire_ajout_podcast();
        }

        // Upload du fichier (src -> filename)
        $src = '';
        if (isset($_FILES['userfile']) && ($_FILES['userfile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $originalName = (string)$_FILES['userfile']['name'];
            $tmpName = (string)$_FILES['userfile']['tmp_name'];
            $mimeType = (string)($_FILES['userfile']['type'] ?? '');
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($ext !== 'mp3' || ($mimeType !== '' && $mimeType !== 'audio/mpeg')) {
                return '<p>Fichier invalide : seul le MP3 est accepté.</p>' . $this->formulaire_ajout_podcast();
            }

            $projectRoot = dirname(__DIR__, 3);
            $audioDir = $projectRoot . DIRECTORY_SEPARATOR . 'audio';
            if (!is_dir($audioDir)) {
                @mkdir($audioDir, 0755, true);
            }

            try { $basename = bin2hex(random_bytes(16)); } catch (\Throwable) { $basename = uniqid('', true); }
            $targetFilename = $basename . '.mp3';
            $targetPath = $audioDir . DIRECTORY_SEPARATOR . $targetFilename;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                return '<p>Erreur lors de l’upload du fichier.</p>' . $this->formulaire_ajout_podcast();
            }
            $src = $targetFilename;
        } else {
            return '<p>Aucun fichier uploadé.</p>' . $this->formulaire_ajout_podcast();
        }

        // Parse durée (accept mm:ss ou secondes)
        $duration = 0;
        if ($durationRaw !== '') {
            if (preg_match('/^(\d+):([0-5]\d)(?::([0-5]\d))?$/', $durationRaw, $m)) {
                $h = isset($m[3]) ? (int)$m[1] : 0;
                $m2 = isset($m[3]) ? (int)$m[2] : (int)$m[1];
                $s = isset($m[3]) ? (int)$m[3] : (int)$m[2];
                $duration = $h ? ($h*3600 + $m2*60 + $s) : ($m2*60 + $s);
            } elseif (ctype_digit($durationRaw)) {
                $duration = (int)$durationRaw;
            }
        }

        // Création de la PodcastTrack: constructeur (titre, filename) puis setters magiques
        $track = new \iutnc\deefy\audio\tracks\PodcastTrack($title, $src);
        if ($genre !== '')   { $track->genre = $genre; }
        if ($duration >= 0)  { $track->duree = $duration; }
        if ($auteur !== '')  { $track->auteur = $auteur; }
        if ($date !== '')    { $track->date = $date; }

        try {
            $repo = \iutnc\deefy\repository\DeefyRepository::getInstance();
            // Enregistrer la piste et la lier à la playlist
            // Adapte cette ligne au prototype réel (ex: saveTrack($track, $playlistId))
            $this->save_database($track);

            // Recharger et afficher la playlist
            $pl = $repo->findPlaylistById($playlistId);
            if (!$pl) return '<p>Playlist introuvable après ajout.</p>';

            return (new \iutnc\deefy\render\AudioListRenderer($pl))->render()
                 . '<p><a href="?action=add-podcast-track&id=' . $playlistId . '">Ajouter un autre podcast</a></p>';

        } catch (\Throwable $e) {
            return '<p>Erreur lors de l’enregistrement: ' . htmlspecialchars($e->getMessage()) . '</p>'
                 . $this->formulaire_ajout_podcast();
        }
    }

    public function formulaire_ajout_podcast(): string
    {
        $html = '<form method="post" enctype="multipart/form-data">
                    <label>Titre : <input type="text" name="title" required></label><br>
                    <label>Auteur : <input type="text" name="author"></label><br>
                    <label>Durée (sec) : <input type="number" name="duration" min="0"></label><br>
                    <label>Fichier audio (MP3) : <input type="file" name="userfile" accept=".mp3,audio/mpeg"></label><br>
                    <button type="submit">Ajouter la piste</button>
                 </form>';

                 /*

                 constructeur :
                    protected string $titre;
                    protected string $filename;

                setter magiques :
                    protected string $genre;
                    protected int $duree;
                    protected string $auteur;
                    protected string $date;
                */

        return $html;
    }

    public function save_database(PodcastTrack $track): void
    {
        $repo=DeefyRepository::getInstance();
        $repo->saveTrack($track,(string) $_SESSION['playlist']);
    }

}
