<?php

declare(strict_types=1);

namespace App\Actions\Forge;

use App\Actions\MergeEnvironmentVariables;
use App\Actions\TextToArray;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Illuminate\Support\Facades\Http;

class UpdateForgeEnvironmentVariables
{
    use Outputifier;

    /**
     * Handle the update of environment variables in a Forge site.
     *
     * @param ForgeService $service
     * @return bool
     */
    public function handle(ForgeService $service): bool
    {
        $newKeys = array_merge(
            TextToArray::run($service->setting->envKeys),
            $service->database
        );

        if ($service->setting->sslRequired) {
            $newKeys = array_merge($newKeys, ['APP_URL' => $service->getSiteLink()]);
        }

        if (empty($newKeys)) {
            return false;
        }

        $source = $service->forge->siteEnvironmentFile($service->server->id, $service->site->id);
        $mergedEnvs = MergeEnvironmentVariables::run($source, $newKeys);

        // TODO: This is a temporary solution. Direct HTTP call to Forge API should be replaced
        // with proper SDK support once available. Remove this conditional when SDK supports
        // organization-based endpoints.
        $useNewApi = env('USE_NEW_FORGE_API', false) === true || env('USE_NEW_FORGE_API') === 'true';
        $organization = env('FORGE_ORGANIZATION');

        if ($useNewApi && $organization) {
            $url = sprintf(
                'https://forge.laravel.com/api/orgs/%s/servers/%s/sites/%s/environment',
                $organization,
                $service->server->id,
                $service->site->id
            );

            Http::withHeaders([
                'Authorization' => sprintf('Bearer %s', $service->setting->token),
                'Content-Type' => 'application/json',
            ])->put($url, [
                'environment' => $mergedEnvs,
                'cache' => true,
                'queues' => true,
            ]);
        } else {
            $service->forge->updateSiteEnvironmentFile(
                $service->server->id,
                $service->site->id,
                $mergedEnvs
            );
        }

        return true;
    }
}
