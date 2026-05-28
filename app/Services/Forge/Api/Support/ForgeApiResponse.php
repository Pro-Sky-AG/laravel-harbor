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
        $rawErrors = $payload['errors'] ?? [];

        // Forge may return JSON:API errors ([{detail, title}]) or Laravel validation
        // errors ({field: [msg, ...]}).  Normalise both into a flat string list.
        $errors = collect($rawErrors)
            ->flatMap(function (mixed $error): array {
                if (is_array($error) && (isset($error['detail']) || isset($error['title']))) {
                    // JSON:API error object
                    return [$error['detail'] ?? $error['title']];
                }

                if (is_array($error)) {
                    // Laravel validation bag entry: array of message strings
                    return array_values($error);
                }

                return [(string) $error];
            })
            ->values()
            ->all();

        $message = $errors !== [] ? implode(PHP_EOL, $errors) : (string) ($payload['message'] ?? 'Forge API request failed.');

        if ($response->status() === 422) {
            throw new ForgeValidationException($message, 422, $errors);
        }

        throw new ForgeApiException($message, $response->status(), $errors);
    }
}
