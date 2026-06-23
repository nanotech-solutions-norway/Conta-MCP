<?php

declare(strict_types=1);

final class Security
{
    public static function applyCors(Config $config): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigin = $config->allowedOrigin();

        if ($allowedOrigin !== '' && $origin === $allowedOrigin) {
            header('Access-Control-Allow-Origin: ' . $allowedOrigin);
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Headers: Authorization, Content-Type, Mcp-Session-Id, MCP-Protocol-Version');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Max-Age: 600');
    }

    public static function handleOptions(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function requireBearerToken(Config $config): ?array
    {
        $expected = $config->bearerToken();
        if ($expected === '') {
            return [
                'code' => 500,
                'error' => 'mcp_bearer_token_missing',
                'message' => 'MCP bearer token is not configured server-side.',
            ];
        }

        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return [
                'code' => 401,
                'error' => 'missing_bearer_token',
                'message' => 'Authorization header must use Bearer token authentication.',
            ];
        }

        $provided = trim($matches[1]);
        if (!hash_equals($expected, $provided)) {
            return [
                'code' => 403,
                'error' => 'invalid_bearer_token',
                'message' => 'Invalid MCP bearer token.',
            ];
        }

        return null;
    }
}
