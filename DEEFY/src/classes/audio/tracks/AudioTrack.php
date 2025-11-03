<?php
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AudioTrack {
    protected string $titre;
    protected string $genre;
    protected int $duree;
    protected string $filename;

    public function __construct(string $titre, string $filename) {
        $this->titre = $titre;
        $this->filename = $filename;
        $this->duree = 0;
        $this->genre = "";
    }

    

    public function __toString(): string {
        return json_encode(get_object_vars($this));
    }

    public function __get(string $name) {
        if (!property_exists($this, $name)) {
            throw new InvalidPropertyNameException("invalid property : $name");
        }
        return $this->$name;
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
            case 'filename':
                $this->$name = (string)$value;
                break;

            default:
                throw new InvalidPropertyNameException("invalid property : {$name}");
        }
    }
}
