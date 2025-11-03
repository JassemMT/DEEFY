<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\lists\AudioList;

class Playlist extends AudioList {

    public function ajouterPiste(AudioTrack $piste): void {
        foreach ($this->pistes as $p) {
            if ($p === $piste) return; // évite doublon exact
        }
        $this->pistes[] = $piste;
        $this->nb_pistes++;
        $this->duree_totale += $piste->duree;
    }

    public function supprimerPiste(int $index): void {
        if (isset($this->pistes[$index])) {
            $this->duree_totale -= $this->pistes[$index]->duree;
            unset($this->pistes[$index]);
            $this->pistes = array_values($this->pistes);
            $this->nb_pistes = count($this->pistes);
        }
    }

    public function ajouterListe(array $liste): void {
        foreach ($liste as $piste) {
            if ($piste instanceof AudioTrack && !in_array($piste, $this->pistes, true)) {
                $this->ajouterPiste($piste);
            }
        }
    }
}
