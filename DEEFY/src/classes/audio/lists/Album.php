<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;

class Album extends AudioList {
    protected string $artiste;
    protected string $date_sortie;

    public function __construct(string $nom, array $pistes) {
        parent::__construct($nom, $pistes);
        $this->artiste = "";
        $this->date_sortie = "";
    }

    public function setArtiste(string $artiste): void {
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $date): void {
        $this->date_sortie = $date;
    }
}
