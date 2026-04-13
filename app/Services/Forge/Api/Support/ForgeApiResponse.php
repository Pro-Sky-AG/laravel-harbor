<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Support;

use App\Services\Forge\Api\Exceptions\ForgeApiException;
use App\Services\Forge\Api\Exceptions\ForgeValidationException;
use Saloon\Http\Response;

class ForgeApiResponse
{
    public static function payload(Response $response): array
    {
        if (! $response->successful()) {
            static::throwFrom($response);
        }

        $body = trim($response->body());

        if ($body === '') {
            return [];
        }

        return $response->json();
    }

    protected static function throwFrom(Response $response): never
    {
        $payload = $response->json();
        $errors = collect($payload['errors'] ?? [])
            ->map(fn (array $error): string => $error['detail'] ?? $error['title'] ?? 'Unknown API error')
            ->values()
            ->all();

        $message = $errors !== [] ? implode(PHP_EOL, $errors) : (string) ($payload['message'] ?? 'Forge API request failed.');

        if ($response->status() === 422) {
            throw new ForgeValidationException($message, 422, $errors);
        }

        throw new ForgeApiException($message, $response->status(), $errors);
    }
}
