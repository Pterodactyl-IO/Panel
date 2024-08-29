<?php

namespace Pterodactyl\Contracts\Repository;

use Pterodactyl\Models\User;
use Illuminate\Support\Collection;

interface ApiKeyRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all the account API keys that exist for a specific user.
     */
    public function getAccountKeys(User $user): Collection;

    /**
     * Get all the application API keys that exist.
     */
    public function getApplicationKeys(): Collection;

    /**
     * Delete an account API key from the panel for a specific user.
     */
    public function deleteAccountKey(User $user, string $identifier): int;

    /**
     * Delete an application API key from the panel.
     */
    public function deleteApplicationKey(string $identifier): int;
}
