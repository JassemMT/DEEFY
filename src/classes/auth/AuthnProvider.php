<?php
declare(strict_types=1);

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class AuthnProvider {

    public static function signin(string $email, string $password): void {
        $repo = DeefyRepository::getInstance();

        $user = $repo->findUserByEmail($email);
        if (!$user) {
            throw new AuthnException('User not found', AuthnException::USER_NOT_FOUND);
        }

        $hash = $user['password'] ?? $user['passwd'] ?? null;
        if (!is_string($hash) || $hash === '' || !password_verify($password, $hash)) {
            throw new AuthnException('Invalid credentials', AuthnException::INVALID_CREDENTIALS);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'email' => $user['email'],
            'role'  => isset($user['role']) ? (int)$user['role'] : 1
        ];
    }

    public static function getSignedInUser(): array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
            throw new AuthnException('Aucun utilisateur authentifié', AuthnException::USER_NOT_FOUND);
        }
        return $_SESSION['user'];
    }

    public static function isLoggedIn(): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ?action=signin');
            exit();
        }
    }

    public static function register(string $email, string $password): int
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthnException('Email invalide', AuthnException::USER_NOT_FOUND);
        }

        if (mb_strlen($password) < 10) {
            throw new AuthnException('Mot de passe trop court (min 10 caractères)', AuthnException::INVALID_CREDENTIALS);
        }

        $repo = DeefyRepository::getInstance();
        $existing = $repo->findUserByEmail($email);
        if ($existing) {
            throw new AuthnException('Un compte existe déjà pour cet email', AuthnException::INVALID_CREDENTIALS);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT, []);
        if ($hash === false) {
            throw new AuthnException('Erreur de hachage du mot de passe', AuthnException::INVALID_CREDENTIALS);
        }

        $id = $repo->createUser($email, $hash, 1);
        if (isset($id) === false) {
            throw new AuthnException('Erreur création utilisateur', AuthnException::INVALID_CREDENTIALS);
        }

        AuthnProvider::signin($email, $password);

        return (int)$id;
    }
}