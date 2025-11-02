<?php
namespace iutnc\deefy\audio\tracks;
use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AlbumTrack extends AudioTrack {
    protected string $artiste;
    protected string $album;
    protected int $annee;
    protected int $numero_piste;

    public function __construct(string $titre, string $filename, string $album, int $numero_piste, string $artiste = "", int $annee = 0) {
        parent::__construct($titre, $filename);
        $this->album = $album;
        $this->numero_piste = $numero_piste;
        $this->artiste = "";
        $this->annee = 0;
    }

    public function __get(string $name) {
        if (!property_exists($this, $name)) {
            throw new \iutnc\deefy\exception\InvalidPropertyNameException("invalid property : $name");
        }
        return $this->$name;
    }

    public function __set(string $name, $value): void {
        if (!property_exists($this, $name)) {
            throw new \iutnc\deefy\exception\InvalidPropertyNameException("invalid property : $name");
        }

        switch ($name) {
            case 'artiste':
            case 'album':
                $this->$name = (string)$value;
                break;

            case 'annee':
            case 'numero_piste':
                $v = (int)$value;
                if ($v < 0) {
                    throw new \iutnc\deefy\exception\InvalidPropertyValueException("$name doit être >= 0");
                }
                $this->$name = $v;
                break;

            default:
                parent::__set($name, $value);
        }
    }
}
