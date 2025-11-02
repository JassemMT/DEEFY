<?php

require_once  __DIR__ . '/vendor/autoload.php';

\iutnc\deefy\repository\DeefyRepository::setConfig(__DIR__ . '/config/config.db.ini');

$repo = \iutnc\deefy\repository\DeefyRepository::getInstance();

$playlists = $repo->findAllPlaylists();
foreach ($playlists as $pl) {
    print $pl['nom'] . "<strong>";
}

//Playlist vide
$pl = new \iutnc\deefy\audio\lists\Playlist('test');
$pl = $repo->savePlaylist($pl, $_SESSION['user_id'] ?? 1);
print "playlist  : " . $pl->nom . ":". $pl->id . "\n";

$track = new \iutnc\deefy\audio\tracks\PodcastTrack('test', 'test.mp3', 'auteur', '2021-01-01', 10, 'genre');
$track = $repo->savePodcastTrack($track);
print "track 2 : " . $track->titre . ":". get_class($track). "\n";
$repo->addTrackToPlaylist($pl->id, $track->id);

