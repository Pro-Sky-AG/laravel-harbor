<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Support;

class JsonApiData
{
    public static function data(array $payload): array
    {
        return $payload['data'] ?? [];
    }

    public static function attributes(array $resource): array
    {
        return $resource['attributes'] ?? [];
    }

    public static function id(array $resource): string|int
    {
        if (! isset($resource['id'])) {
            throw new \InvalidArgumentException(
                'Missing required "id" field in API resource: ' . json_encode($resource)
            );
        }

        return $resource['id'];
    }

    public static function relationshipIds(array $resource, string $relationship): array
    {
        $items = $resource['relationships'][$relationship]['data'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        if (isset($items['id'])) {
            return [$items['id']];
        }

        return collect($items)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    public static function nextCursor(array $payload): ?string
    {
        return $payload['meta']['next_cursor'] ?? null;
    }
}
