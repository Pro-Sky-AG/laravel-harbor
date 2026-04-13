<?php

declare(strict_types=1);

namespace App\Services\Forge\Data;

use App\Services\Forge\Api\Support\JsonApiData;
use Illuminate\Support\Str;

class ForgeSiteData
{
    public function __construct(
        public int|string $id,
        public string $name,
        public ?string $status,
        public string $username,
        public ?string $repository,
        public ?string $webDirectory,
        public ?string $rootDirectory,
        public ?string $directory,
        public ?string $deploymentUrl,
    ) {
        //
    }

    public static function fromResource(array $resource): self
    {
        $attributes = JsonApiData::attributes($resource);
        $webDirectory = $attributes['web_directory'] ?? null;
        $rootDirectory = $attributes['root_directory'] ?? null;

        $directory = null;

        if (is_string($webDirectory) && is_string($rootDirectory)) {
            $directory = Str::replaceFirst($rootDirectory, '', $webDirectory) ?: '/';
        }

        return new self(
            id: JsonApiData::id($resource),
            name: (string) ($attributes['name'] ?? ''),
            status: $attributes['status'] ?? null,
            username: (string) ($attributes['user'] ?? ''),
            repository: $attributes['repository'] ?? null,
            webDirectory: $webDirectory,
            rootDirectory: $rootDirectory,
            directory: $directory,
            deploymentUrl: $attributes['deployment_url'] ?? null,
        );
    }
}
