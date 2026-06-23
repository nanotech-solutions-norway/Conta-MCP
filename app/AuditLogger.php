<?php

declare(strict_types=1);

final class AuditLogger
{
    public function __construct(private readonly string $logPath)
    {
    }

    public function record(string $event, array $metadata = []): void
    {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        $safeMetadata = $this->redact($metadata);
        $entry = [
            'timestamp_utc' => gmdate('c'),
            'event' => $event,
            'metadata' => $safeMetadata,
        ];

        @file_put_contents(
            $this->logPath,
            json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private function redact(array $metadata): array
    {
        $blockedKeys = ['apiKey', 'api_key', 'conta_api_key', 'authorization', 'bearer', 'token', 'password', 'secret'];

        $walker = function (mixed $value, ?string $key = null) use (&$walker, $blockedKeys): mixed {
            if ($key !== null && in_array(strtolower($key), $blockedKeys, true)) {
                return '[REDACTED]';
            }
            if (is_array($value)) {
                $out = [];
                foreach ($value as $childKey => $childValue) {
                    $out[$childKey] = $walker($childValue, is_string($childKey) ? $childKey : null);
                }
                return $out;
            }
            if (is_string($value) && strlen($value) > 250) {
                return substr($value, 0, 250) . '...[truncated]';
            }
            return $value;
        };

        return $walker($metadata);
    }
}
