<?php
namespace iutnc\deefy\repository;

class DeefyRepository{
    
    private \PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct( array $conf ){
        $this->pdo = new \PDO( $conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function getInstance(){
        if (is_null(self::$instance)){
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig( $file ){
        $conf = parse_ini_file( $file );

        if($conf===false){
            throw new \Exception("Fichier de configuration introuvable");
        }

        $driver = $conf['driver'] ?? 'mysql';
        $host   = $conf['host'] ?? 'localhost';
        $dbname = $conf['database'] ?? '';
        if (!$dbname) {
            throw new \RuntimeException("Le fichier de configuration doit contenir 'database'.");
        }

        $dsn = "$driver:host=$host;dbname=$dbname";
        if (!empty($conf['charset'])) {
            $dsn .= ";charset={$conf['charset']}";
        }

        self::$config = [
            'dsn'  => $dsn,
            'user' => $conf['username'] ?? $conf['user'] ?? '',
            'pass' => $conf['password'] ?? $conf['pass'] ?? ''
        ];
    }

    public function findAllPlaylists(): array {
        $stmt = $this->pdo->query("SELECT * FROM playlist");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAllPlaylistByUserId( int $id_user, ?bool $objet = false ): array {

        $stmt = $this->pdo->prepare("SELECT p.* FROM playlist p
            JOIN user2playlist up ON p.id = up.id_pl
            WHERE up.id_user = :id_user");
        
        $stmt->execute(['id_user' => $id_user]);
        $playlistsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$objet) {
            return $playlistsData;
        } else {
            $playlists = [];
            foreach ($playlistsData as $data) {
                $playlist = new \iutnc\deefy\audio\lists\Playlist($data['nom'], findAllTracksByIdPlaylist($data['id'], true));
                $playlists[] = $playlist;
            }
                return $playlists;
        }
    }

  public function findAllTracksByIdPlaylist( int $id_playlist, ?bool $objet = false ): array {
        $stmt = $this->pdo->prepare("SELECT t.* FROM track t
            JOIN playlist2track pt ON t.id = pt.id_track
            WHERE pt.id_pl = :id_pl
            ORDER BY pt.no_piste_dans_liste ASC");
        
        $stmt->execute(['id_pl' => $id_playlist]);
        $tracksData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$objet) {
            return $tracksData;
        } else {
            $tracks = [];
            foreach ($tracksData as $data) {
                // Créer des objets AudioTrack ou ses sous-classes selon le type
                if ($data['type'] === 'album') {
                    $track = new \iutnc\deefy\audio\tracks\AlbumTrack(
                        $data['titre'],
                        $data['filename']);

                        /*
                        protected string $genre;
                        protected int $duree;
                        protected string $artiste;

                        protected string $album;
                        protected int $annee;
                        protected int $numero_piste;
                    */
                        $track->genre = $data['genre'];
                        $track->duree = (int) ($data['duree']);
                        $track->artiste = $data['artiste_album'];
                        $track->album = $data['titre_album'];
                        $track->annee = (int) ($data['annee_album']);
                        $track->numero_piste = (int) ($data['numero_album']);                
                        
                
                }elseif ($data['type'] === 'podcast') {
                    $track = new \iutnc\deefy\audio\tracks\PodcastTrack(
                        $data['titre'],
                        $data['filename']);
                    

                    /*
                    Ajouter avec des setters

                        protected string $genre;
                        protected int $duree;

                        protected string $auteur;
                        protected string $date;
                    */
                    $track->genre = $data['genre'];
                    $track->duree = (int) ($data['duree']);
                    $track->auteur = $data['auteur_podcast'];
                    $track->date = $data['date_podcast'];


                    
                } else {
                    $track = new \iutnc\deefy\audio\tracks\AudioTrack(
                        $data['titre'],
                        $data['filename']
                    );

                       /*
                        
                        protected string $genre;
                        protected int $duree;
                        
                    */
                    $track->genre = $data['genre'];
                    $track->duree = (int) ($data['duree']);
                }
                $tracks[] = $track;
            }
            return $tracks;
        }
    }

    public function savePlaylist(\iutnc\deefy\audio\lists\Playlist $pl, int $id_user): void 
    {
        $name = $pl->nom; // ou $pl->name selon la propriété définie
        
        $stmt = $this->pdo->prepare("INSERT INTO playlist (nom) VALUES (:name)");
        $stmt->execute(['name' => $name]);

        //fetch le id automatique de la ligne insérée dans $id_playlist
        $id_playlist = $this->pdo->lastInsertId();

       

        echo "Playlist saved with ID: " . $id_playlist;

        $stmt = $this->pdo->prepare("INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :id_pl)");
        $stmt->execute(['id_user' => $id_user, 'id_pl' => $id_playlist]);

        $_SESSION['playlist'] = $id_playlist;
    }

    /**
     * Enregistre une piste audio dans la table `track` et optionnellement la lie à une playlist.
     *
     * @param \iutnc\deefy\audio\tracks\AudioTrack $track
     * @param int|null $id_playlist
     * @return void
     */
    public function saveTrack(\iutnc\deefy\audio\tracks\AudioTrack $track, ?string $id_playlist = null): void
    {
        // valeurs communes
        $titre = $track->titre ?? '';
        $genre = $track->genre ?? null;
        $duree = $track->duree ?? null;
        $filename = $track->filename ?? null;

        // colonnes spécifiques initialisées à null
        $type = null;
        $artiste_album = null;
        $titre_album = null;
        $annee_album = null;
        $numero_album = null;
        $auteur_podcast = null;
        $date_podcast = null;

        // détecter le type et remplir les champs spécifiques
        if ($track instanceof \iutnc\deefy\audio\tracks\AlbumTrack) {
            $type = 'album';
            $artiste_album = $track->artiste ?? null;
            $titre_album   = $track->album ?? null;
            $annee_album   = $track->annee ?? null;
            $numero_album  = $track->numero_piste ?? null;
        } elseif ($track instanceof \iutnc\deefy\audio\tracks\PodcastTrack) {
            $type = 'podcast';
            $auteur_podcast = $track->auteur ?? null;
            $date_podcast   = $track->date ?? null;
        } else {
            $type = 'audio';
        }

        // Préparer et exécuter l'insertion dans la table track
        $sql = "INSERT INTO track
            (titre, genre, duree, filename, type, artiste_album, titre_album, annee_album, numero_album, auteur_podcast, date_podcast)
            VALUES
            (:titre, :genre, :duree, :filename, :type, :artiste_album, :titre_album, :annee_album, :numero_album, :auteur_podcast, :date_podcast)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'titre' => $titre,
            'genre' => $genre,
            'duree' => $duree,
            'filename' => $filename,
            'type' => $type,
            'artiste_album' => $artiste_album,
            'titre_album' => $titre_album,
            'annee_album' => $annee_album,
            'numero_album' => $numero_album,
            'auteur_podcast' => $auteur_podcast,
            'date_podcast' => $date_podcast
        ]);

        $id_track = $this->pdo->lastInsertId();

        if ($id_playlist !== null) {
            $stmt2 = $this->pdo->prepare("INSERT INTO playlist2track (id_pl, id_track) VALUES (:id_pl, :id_track)");
            $stmt2->execute(['id_pl' => $id_playlist, 'id_track' => $id_track]);

            /*
            Prochaine étape : ajouter l'attribut 'no_piste_dans_liste' 
            qui se base sur le nombre de pistes déjà présentes dans la playlist
            */
            $stmt3 = $this->pdo->prepare("UPDATE playlist2track SET no_piste_dans_liste = (SELECT COUNT(*) FROM playlist2track WHERE id_pl = :id_pl) WHERE id_pl = :id_pl AND id_track = :id_track");
            $stmt3->execute(['id_pl' => $id_playlist, 'id_track' => $id_track]);
        }

        $_SESSION['playlist'] = $id_playlist;

    }

    public function isPlaylistOwnedByUser(int $playlistId, int $userId): bool
    {
        $sql = "SELECT 1 FROM user2playlist WHERE id_user = :uid AND id_pl = :pid LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $userId, 'pid' => $playlistId]);
        return (bool)$stmt->fetchColumn();
    }

    public function findPlaylistById(int $id): ?\iutnc\deefy\audio\lists\Playlist
    {
        $stmt = $this->pdo->prepare("SELECT * FROM playlist WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) return null;

        $_SESSION['playlist'] = $id;


        //On récupère toutes les pistes de playlist2track qui ont pour id_pl = $id   
        $tracks = $this->findAllTracksByIdPlaylist($id, true);
        $pl = new \iutnc\deefy\audio\lists\Playlist($data['nom'], $tracks);
        return $pl;
        
    }

    public function findUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT id, email, passwd, role FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function createUser(string $email, string $passwordHash, int $role = 1): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO user (email, passwd, role) VALUES (:email, :password, :role)');
        $stmt->execute([
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role
        ]);

        $_SESSION['user'] = [
            'id' => (int)$this->pdo->lastInsertId(),
            'email' => $email
        ];
        return (int)$this->pdo->lastInsertId();
    }



}
