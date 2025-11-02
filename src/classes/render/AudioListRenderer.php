<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    protected AudioList $list;

    // Chemin Absolu Confirmé : /dev_php/DEEFY-main/DEEFY-main/audio/
    private const AUDIO_BASE_PATH = '/audio/'; 

    public function __construct(AudioList $list) {
        $this->list = $list;
    }

    public function render(): string {
        $html = "<h2>{$this->list->nom}</h2><ul>";
        
        foreach ($this->list->pistes as $piste) {
            
            // Le filename est maintenant propre (ex: "chanson.mp3")
            $filename = $piste->filename ?? '';
            
            // Construction du chemin URL : Chemin Absolu + nom de fichier
            $source_url = self::AUDIO_BASE_PATH . $filename; 
            
            $filename_exists = !empty($filename); 
            
            $html .= "<li>";
            $html .= "<h3>{$piste->titre}</h3>";
            $html .= "<p>Durée : {$piste->duree} sec</p>";
            
            // Si le nom de fichier n'est pas vide, on affiche le lecteur audio
            if ($filename_exists) {
                $html .= <<<AUDIO
                    <audio controls preload="none">
                        <source src="{$source_url}" type="audio/mpeg">
                        Votre navigateur ne supporte pas l'élément audio.
                    </audio>
                AUDIO;
            } else {
                $html .= "<p style='color: red;'>Source audio manquante.</p>";
            }

            $html .= "</li>";
        }
        
        $html .= "</ul><p>Total : **{$this->list->nb_pistes} pistes**, {$this->list->duree_totale} sec</p>";
        
        return $html;
    }
}