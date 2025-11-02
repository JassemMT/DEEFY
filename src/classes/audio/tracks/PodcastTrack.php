<?php
namespace iutnc\deefy\audio\tracks;

class PodcastTrack extends AudioTrack {
    protected string $auteur;
    protected string $date;

    public function __construct(string $titre, string $filename) {
        parent::__construct($titre, $filename);
        $this->auteur = "";
        $this->date = "";
    }
}
