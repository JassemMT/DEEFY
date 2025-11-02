<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    protected AudioList $list;

    public function __construct(AudioList $list) {
        $this->list = $list;
    }

    public function render(): string {
        $html = "<h2>{$this->list->nom}</h2><ul>";
        foreach ($this->list->pistes as $piste) {
            $html .= "<li>{$piste->titre} ({$piste->duree} sec)</li>";
        }
        $html .= "</ul><p>Total : {$this->list->nb_pistes} pistes, {$this->list->duree_totale} sec</p>";
        return $html;
    }
}
