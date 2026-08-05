<?php

declare(strict_types=1);

final class ReleaseManifestGuard
{
    public function __construct(
        private readonly Config $config,
        private readonly string $rootDir
    ) {
    }

    public function assertApproved(): string
    {
        $status = $this->status();
        if (($status['ready'] ?? false) !== true) {
            throw new RuntimeException((string) ($status['error'] ?? 'release_manifest_not_ready'));
        }
        return (string) $status['manifest_hash'];
    }

    public function status(): array
    {
        $path = $this->config->approvedReleaseManifestPath();
        if ($path === '' || !is_file($path)) {
            return ['ready' => false, 'error' => 'approved_release_manifest_missing'];
        }

        $raw = file_get_contents($path);
        $manifest = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($manifest)) {
            return ['ready' => false, 'error' => 'approved_release_manifest_invalid'];
        }
        if (($manifest['status'] ?? null) !== 'APPROVED') {
            return ['ready' => false, 'error' => 'release_manifest_not_approved'];
        }

        $checks = [
            'repository_commit' => $this->config->releaseCommit(),
            'write_policy_version' => $this->config->writePolicyVersion(),
            'provider_schema_sha256' => strtolower($this->config->providerSchemaSha256()),
        ];
        foreach ($checks as $key => $expected) {
            if ($expected === '' || !isset($manifest[$key]) || !is_string($manifest[$key]) || !hash_equals($expected, strtolower((string) $manifest[$key]))) {
                return ['ready' => false, 'error' => 'release_manifest_' . $key . '_mismatch'];
            }
        }

        $routeMap = [
            'create_invoice_draft_route' => $this->config->createInvoiceDraftRoute(),
            'readback_invoice_draft_route' => $this->config->readbackInvoiceDraftRoute(),
        ];
        $routeHash = hash('sha256', $this->canonicalJson($routeMap));
        if (!isset($manifest['route_map_sha256']) || !is_string($manifest['route_map_sha256']) || !hash_equals($routeHash, strtolower($manifest['route_map_sha256']))) {
            return ['ready' => false, 'error' => 'release_manifest_route_map_mismatch'];
        }

        $files = $manifest['runtime_files'] ?? null;
        if (!is_array($files) || $files === []) {
            return ['ready' => false, 'error' => 'release_manifest_runtime_files_missing'];
        }
        foreach ($files as $relativePath => $expectedHash) {
            if (!is_string($relativePath) || !is_string($expectedHash) || !preg_match('/^[a-f0-9]{64}$/', strtolower($expectedHash))) {
                return ['ready' => false, 'error' => 'release_manifest_runtime_file_entry_invalid'];
            }
            if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
                return ['ready' => false, 'error' => 'release_manifest_runtime_file_path_invalid'];
            }
            $fullPath = $this->rootDir . '/' . $relativePath;
            if (!is_file($fullPath)) {
                return ['ready' => false, 'error' => 'release_manifest_runtime_file_missing'];
            }
            $actualHash = hash_file('sha256', $fullPath);
            if (!is_string($actualHash) || !hash_equals(strtolower($expectedHash), strtolower($actualHash))) {
                return ['ready' => false, 'error' => 'release_manifest_runtime_file_hash_mismatch'];
            }
        }

        $manifestHash = hash('sha256', $this->canonicalJson($manifest));
        return [
            'ready' => true,
            'error' => null,
            'manifest_hash' => $manifestHash,
            'repository_commit' => $manifest['repository_commit'],
            'runtime_file_count' => count($files),
        ];
    }

    public function buildObservedManifest(array $relativePaths): array
    {
        $runtimeFiles = [];
        foreach ($relativePaths as $relativePath) {
            $relativePath = ltrim((string) $relativePath, '/');
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new InvalidArgumentException('invalid_runtime_file_path');
            }
            $fullPath = $this->rootDir . '/' . $relativePath;
            if (!is_file($fullPath)) {
                throw new RuntimeException('runtime_file_missing:' . $relativePath);
            }
            $runtimeFiles[$relativePath] = hash_file('sha256', $fullPath);
        }
        ksort($runtimeFiles, SORT_STRING);

        $routeMap = [
            'create_invoice_draft_route' => $this->config->createInvoiceDraftRoute(),
            'readback_invoice_draft_route' => $this->config->readbackInvoiceDraftRoute(),
        ];

        return [
            'manifest_version' => '2.0',
            'status' => 'PENDING_OPERATOR_VALIDATION',
            'generated_at_utc' => gmdate('c'),
            'repository_commit' => $this->config->releaseCommit(),
            'write_policy_version' => $this->config->writePolicyVersion(),
            'provider_schema_sha256' => strtolower($this->config->providerSchemaSha256()),
            'route_map' => $routeMap,
            'route_map_sha256' => hash('sha256', $this->canonicalJson($routeMap)),
            'runtime_files' => $runtimeFiles,
            'effective_write_state' => [
                'write_tools_enabled' => $this->config->writeToolsEnabled(),
                'runtime_write_blocked' => $this->config->runtimeWriteBlocked(),
                'execution_allowed' => $this->config->executionAllowed(),
                'production_write_approved' => $this->config->productionWriteApproved(),
            ],
            'approved_by' => 'PENDING_OPERATOR_VALIDATION',
            'approved_at_utc' => 'PENDING_OPERATOR_VALIDATION',
        ];
    }

    private function canonicalJson(array $value): string
    {
        $value = $this->canonicalize($value);
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) {
            throw new RuntimeException('release_manifest_canonicalization_failed');
        }
        return $json;
    }

    private function canonicalize(array $value): array
    {
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $value[$key] = $this->canonicalize($child);
            }
        }
        return $value;
    }
}
