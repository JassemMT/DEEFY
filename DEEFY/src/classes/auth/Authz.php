<?php

declare(strict_types=1);

namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;

class Authz
{
    public const ROLE_ADMIN = 100;

    public static function checkRole(int $expectedRole): bool
    {
        $user = AuthnProvider::getSignedInUser();
        return ((int)$user['role']) === $expectedRole;
    }

    public static function checkPlaylistOwner(int $playlistId): bool
    {
        $user = AuthnProvider::getSignedInUser();
        if ((int)$user['role'] === self::ROLE_ADMIN) {
            return true;
        }
        $repo = DeefyRepository::getInstance();
        return $repo->isPlaylistOwnedByUser($playlistId, (int)$user['id']);
    }
}