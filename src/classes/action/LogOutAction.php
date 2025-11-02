<?php

namespace iutnc\deefy\action;
use iutnc\deefy\auth\AuthnProvider;

class LogOutAction extends Action
{
    public function execute(): string
    {
        AuthnProvider::requireLogin();
        // Properly destroy the session for logout
        session_unset(); // Remove all session variables
        session_destroy(); // Destroy the session
        return '<p>Vous êtes déconnecté.
                <a href="?action=default">Retour à l\'accueil</a></p>';
    }
}