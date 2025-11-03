<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    protected AudioList $list;

    // Chemin Absolu Confirmé : /dev_php/DEEFY-main/DEEFY-main/audio/
    private const AUDIO_BASE_PATH = 'https://webetu.iutnc.univ-lorraine.fr/www/e52526u/DEEFY/audio/'; 

    public function __construct(AudioList $list) {
        $this->list = $list;
    }
   
    public function render(): string {
        $html = "<h2>{$this->list->nom}</h2><ul>";
        $count=0;
        
        foreach ($this->list->pistes as $piste) {
            
            // Le filename est maintenant propre (ex: "chanson.mp3")
            $filename = $piste->filename ?? '';
            
            // Construction du chemin URL : Chemin Absolu + nom de fichier
            $source_url = $filename; 
            
            $filename_exists = !empty($filename); 
            
            $html .= "<li>";
            $html .= "<h4>{$count}</h4>";
            $html .= "<h3>titre : {$piste->titre}</h3>";
            $html .= "<p>Durée : {$piste->duree} sec</p>";
            if($piste instanceof AlbumTrack){
                $html .= "<p>auteur : {$piste->artiste} </p>";

            } else if($piste instanceof PodcastTrack){
                  $html .= "<p>auteur : {$piste->auteur} </p>";
            }

            $html .= "<p>genre : {$piste->genre} </p>";

            $count++;


            
            // Si le nom de fichier n'est pas vide, on affiche le lecteur audio
            if ($filename_exists) {
                $html .= '
                    <audio controls preload="none">
                        <source src="' . self::AUDIO_BASE_PATH . $source_url . '" type="audio/mpeg" required>
                        Votre navigateur ne supporte pas l\'élément audio.
                    </audio>'
                ;
            } else {
                $html .= "<p style='color: red;'>Source audio manquante.</p>";
            }

            $html .= "</li>";
        }
        
        $html .= "</ul><p>Total : **{$this->list->nb_pistes} pistes**, {$this->list->duree_totale} sec</p>";
        
        return $html;
    }
}