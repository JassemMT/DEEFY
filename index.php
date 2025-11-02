<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

\iutnc\deefy\repository\DeefyRepository::setConfig(__DIR__ . '/config/config.db.ini');


use iutnc\deefy\dispatch\Dispatcher as Dispatcher;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
 }

//LA on verifie que y'a bien un parametre php action
$action =(isset($_GET['action']))?$_GET['action']:'';

//On créé le dispatcher
$app = new Dispatcher($action);
$app->run();
