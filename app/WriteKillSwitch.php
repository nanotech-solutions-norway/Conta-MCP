<?php

declare(strict_types=1);

final class WriteKillSwitch
{
    public function __construct(private readonly Config $config)
    {
    }

    public function assertActionOpen(string $action): void
    {
        $status = $this->status($action);
        if (($status['open'] ?? false) !== true) {
            throw new RuntimeException((string) ($status['error'] ?? 'write_kill_switch_blocked'));
        }
    }

    public function status(?string $action = null): array
    {
        $path = $this->config->writeKillSwitchPath();
        if ($path === '' || !is_file($path)) {
            return ['open' => false, 'error' => 'write_kill_switch_file_missing'];
        }
        $raw = file_get_contents($path);
        $document = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($document)) {
            return ['open' => false, 'error' => 'write_kill_switch_file_invalid'];
        }
        if (($document['globalBlocked'] ?? true) !== false) {
            return ['open' => false, 'error' => 'write_kill_switch_global_blocked', 'reason' => $document['reason'] ?? null];
        }
        $blockedActions = $document['blockedActions'] ?? [];
        if (!is_array($blockedActions)) {
            return ['open' => false, 'error' => 'write_kill_switch_actions_invalid'];
        }
        if ($action !== null && in_array($action, array_map('strval', $blockedActions), true)) {
            return ['open' => false, 'error' => 'write_kill_switch_action_blocked', 'reason' => $document['reason'] ?? null];
        }
        return ['open' => true, 'error' => null, 'updated_at_utc' => $document['updatedAtUtc'] ?? null];
    }
}
