<?php
namespace iutnc\deefy\audio\tracks;
use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class PodcastTrack extends AudioTrack {
    protected string $auteur;
    protected string $date;

    public function __construct(string $titre, string $filename) {
        parent::__construct($titre, $filename);
        $this->auteur = "";
        $this->date = "";
    }

     public function __set(string $name, $value): void {
        if (!property_exists($this, $name)) {
            throw new InvalidPropertyNameException("invalid property : $name");
        }

        switch ($name) {
            case 'duree':
                $v = (int)$value;
                if ($v < 0) {
                    throw new InvalidPropertyValueException("duree doit être >= 0");
                }
                $this->duree = $v;
                break;

            case 'titre':
            case 'genre':
            case 'auteur':
            case 'date':
            case 'filename':
                $this->$name = (string)$value;
                break;

            default:
                throw new InvalidPropertyNameException("invalid property : {$name}");
        }
    }
}
