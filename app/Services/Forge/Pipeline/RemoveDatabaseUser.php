<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Harbor.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class RemoveDatabaseUser
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $this->information('Starting RemoveDatabaseUser pipeline.');
        $expectedName = $service->getFormattedDatabaseName();
        $users = $service->databaseUsers();

        $this->information(sprintf('Looking for database user "%s" among %d user(s): %s',
            $expectedName,
            count($users),
            implode(', ', array_map(fn ($u) => $u->name, $users))
        ));

        foreach ($users as $databaseUser) {
            if ($databaseUser->name === $expectedName) {
                $this->information('Removing database with user.');

                foreach ($service->databases() as $database) {
                    if ($database->name === $expectedName) {
                        $service->deleteDatabase($database->id);
                    }
                }

                $service->deleteDatabaseUser($databaseUser->id);
            }
        }

        return $next($service);
    }
}
