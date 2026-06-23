<?php

declare(strict_types=1);

final class HttpClient
{
    public function request(string $method, string $url, array $headers = [], ?array $jsonBody = null, int $timeoutSeconds = 20): array
    {
        $method = strtoupper($method);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL.');
        }

        $requestHeaders = [];
        foreach ($headers as $name => $value) {
            if ($value !== null && $value !== '') {
                $requestHeaders[] = $name . ': ' . $value;
            }
        }

        $body = null;
        if ($jsonBody !== null) {
            $body = json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                throw new InvalidArgumentException('Could not JSON-encode request body.');
            }
            $requestHeaders[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('HTTP request failed: ' . $curlError);
        }

        $decoded = null;
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
        }

        return [
            'status' => $status,
            'ok' => $status >= 200 && $status < 300,
            'body' => is_array($decoded) ? $decoded : $raw,
        ];
    }
}
