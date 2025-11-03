<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;

class AudioList {
    protected string $nom;
    protected int $nb_pistes;
    protected int $duree_totale;
    protected array $pistes;

    public function __construct(string $nom, array $pistes = []) {
        $this->nom = $nom;
        $this->pistes = $pistes;
        $this->nb_pistes = count($pistes);
        $this->duree_totale = array_sum(array_map(fn($p) => $p->duree, $pistes));
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("invalid property : $name");
    }
}
