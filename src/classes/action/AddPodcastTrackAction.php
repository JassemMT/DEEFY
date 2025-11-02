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
        $repo = \iutnc\deefy\repository\DeefyRepository::getInstance();

        $html = '';

        // POST : traitement du formulaire -> création de la piste, ajout à la playlist en session et affichage
        if (($this->http_method ?? $_SERVER['REQUEST_METHOD']) === 'POST') {
            // Récupération et nettoyage des champs
            $rawTitle   = $_POST['title'] ?? '';
            $rawAuthor  = $_POST['author'] ?? '';
            $rawDuration = $_POST['duration'] ?? '';

            $title  = trim(filter_var((string)$rawTitle, FILTER_SANITIZE_SPECIAL_CHARS));
            $author = trim(filter_var((string)$rawAuthor, FILTER_SANITIZE_SPECIAL_CHARS));
            $duration = (int) $rawDuration;

            if ($title === '') {
                return '<p>Titre invalide.</p>' . $this->formulaire_ajout_piste();
            }

            // gestion de l'upload (optionnel) : input name = userfile
            $src = trim(filter_var((string)($_POST['src'] ?? ''), FILTER_SANITIZE_URL)); // par défaut si aucun upload

            if (isset($_FILES['userfile']) && isset($_FILES['userfile']['error']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['userfile']['name'];
                $tmpName = $_FILES['userfile']['tmp_name'];
                $mimeType = $_FILES['userfile']['type'] ?? '';
                $origLower = strtolower((string)$originalName);

                // vérifications simples demandées
                $hasMp3Ext = substr($origLower, -4) === '.mp3';
                $isAudioMpeg = $mimeType === 'audio/mpeg';

                // interdiction d'uploader un .php (vérifier extension)
                $origExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?? '');
                if ($origExt === 'php') {
                    return '<p>Type de fichier non autorisé.</p>' . $this->formulaire_ajout_piste();
                }

                if (! $hasMp3Ext || ! $isAudioMpeg) {
                    return '<p>Fichier invalide : seul le MP3 est accepté.</p>' . $this->formulaire_ajout_piste();
                }

                // préparer dossier cible /audio à la racine du projet
                $projectRoot = dirname(__DIR__, 3);
                $audioDir = $projectRoot . DIRECTORY_SEPARATOR . 'audio';
                if (!is_dir($audioDir)) {
                    // tenter de créer le dossier si absent
                    @mkdir($audioDir, 0755, true);
                }

                // nom aléatoire
                try {
                    $random = bin2hex(random_bytes(16));
                } catch (\Throwable $e) {
                    $random = uniqid('', true);
                }
                $targetFilename = $random . '.mp3';
                $targetPath = $audioDir . DIRECTORY_SEPARATOR . $targetFilename;

                // déplacer le fichier uploadé
                if (!move_uploaded_file($tmpName, $targetPath)) {
                    return '<p>Erreur lors de l\'upload du fichier.</p>' . $this->formulaire_ajout_piste();
                }

                // src utilisé par la piste (chemin relatif web)
                $src = '../audio/' . $targetFilename;
            }

            // Instanciation de la piste (signature : title, author, duration, src)
            $track = new PodcastTrack($title, $author, $duration, $src);

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            
           

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }


            $playlist = $_SESSION['playlist'] ?? null;

            if ($playlist == null) {
                return '<p>Aucune playlist trouvée. Créez une playlist avant d’ajouter des pistes.</p>';
            }
            
            $repo=DeefyRepository::getInstance();

            $this->save_database($track);
            
            $html .= (new AudioListRenderer($repo->findPlaylistById($playlist)))->render();
            $html .= '<p><a href="?action=add-track">Ajouter encore une piste</a></p>';

            return $html;
        }

        // Sinon : afficher le formulaire d'ajout de piste
        $html .= $this->formulaire_ajout_piste();
        return $html;
    }

    public function formulaire_ajout_piste(): string
    {
        $html = '<form method="post" enctype="multipart/form-data">
                    <label>Titre : <input type="text" name="title" required></label><br>
                    <label>Auteur : <input type="text" name="author"></label><br>
                    <label>Durée (sec) : <input type="number" name="duration" min="0"></label><br>
                    <label>Fichier audio (MP3) : <input type="file" name="userfile" accept=".mp3,audio/mpeg"></label><br>
                    <button type="submit">Ajouter la piste</button>
                 </form>';

        return $html;
    }

    public function save_database(PodcastTrack $track): void
    {
        $repo=DeefyRepository::getInstance();
        $repo->saveTrack($track,(string) $_SESSION['playlist']);
    }

}
