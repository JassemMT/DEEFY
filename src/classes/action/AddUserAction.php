<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\action\Action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class AddUserAction extends Action
{
    public function execute(): string
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $emailRaw = $_POST['email'] ?? '';
            $pw1 = $_POST['password'] ?? '';
            $pw2 = $_POST['password_confirm'] ?? '';

            $email = trim(filter_var((string)$emailRaw, FILTER_SANITIZE_EMAIL));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return '<p>Email invalide.</p>' . $this->renderForm();
            }

            if (mb_strlen($pw1) < 10) {
                return '<p>Le mot de passe doit contenir au moins 10 caractères.</p>' . $this->renderForm();
            }

            if ($pw1 !== $pw2) {
                return '<p>Les mots de passe ne correspondent pas.</p>' . $this->renderForm();
            }
            

            try {
                $id = AuthnProvider::register($email, $pw1);
                return '<p>Inscription réussie. ID utilisateur créé : ' . htmlspecialchars((string)$id, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') .
                    ' — <a href="?action=signin">Se connecter</a></p>';
            } catch (AuthnException $e) {
                return '<p>Erreur inscription : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '</p>'
                    . $this->renderForm();
            } catch (\Throwable $t) {
                return '<p>Erreur interne.</p>' . $t->getMessage() . $this->renderForm();
            }
        }

        return $this->renderForm();
    }

    private function renderForm(): string
    {
        return <<<HTML
        <form method="post" action="?action=add-user">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Mot de passe: <input type="password" name="password" required></label><br>
        <label>Confirmer mot de passe: <input type="password" name="password_confirm" required></label><br>
        <button type="submit">S'inscrire</button>
        </form>
        HTML;
    }
}