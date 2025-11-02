<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SignInAction extends Action {
    public function execute(): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            try {
                AuthnProvider::signin($email, $password);
                // Rediriger vers la page d'accueil après connexion réussie
                header('Location: ?action=default');
                exit;
            } catch (AuthnException $e) {
                return sprintf(
                    '<p class="error">Erreur : %s</p>%s',
                    htmlspecialchars($e->getMessage()),
                    $this->renderForm()
                );
            }
        }

        return $this->renderForm();
    }

    private function renderForm(): string {
        return <<<HTML
        <form method="post" action="?action=signin">
            <div>
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        HTML;
    }
}