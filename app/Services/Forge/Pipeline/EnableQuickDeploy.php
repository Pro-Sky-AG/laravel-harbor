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

use App\Services\Forge\Api\Exceptions\ForgeApiException;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class EnableQuickDeploy
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->quickDeploy || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $this->information('Enabling the quick deploy.');

        try {
            $service->enableQuickDeploy();
        } catch (ForgeApiException $e) {
            if (str_contains($e->getMessage(), 'identical hook already exists')) {
                $this->information('Quick deploy hook already exists, skipping.');
            } else {
                throw $e;
            }
        }

        return $next($service);
    }
}
