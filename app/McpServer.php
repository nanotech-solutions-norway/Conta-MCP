<?php

declare(strict_types=1);

final class McpServer
{
    private const PROTOCOL_VERSION = '2025-06-18';

    public function __construct(
        private readonly Config $config,
        private readonly ContaTools $tools,
        private readonly AuditLogger $auditLogger
    ) {
    }

    public function handle(): void
    {
        Security::applyCors($this->config);
        Security::handleOptions();

        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
            echo json_encode([
                'name' => 'conta-mcp-server',
                'status' => 'ready',
                'protocolVersion' => self::PROTOCOL_VERSION,
                'configured' => $this->config->isConfigured(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'method_not_allowed']);
            return;
        }

        $authError = Security::requireBearerToken($this->config);
        if ($authError !== null) {
            http_response_code((int) $authError['code']);
            echo json_encode($authError, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return;
        }

        $raw = file_get_contents('php://input') ?: '';
        $request = json_decode($raw, true);
        if (!is_array($request)) {
            echo json_encode($this->error(null, -32700, 'Parse error', 'Request body must be valid JSON.'));
            return;
        }

        if (array_is_list($request)) {
            $responses = [];
            foreach ($request as $item) {
                if (is_array($item)) {
                    $response = $this->handleOne($item);
                    if ($response !== null) {
                        $responses[] = $response;
                    }
                } else {
                    $responses[] = $this->error(null, -32600, 'Invalid Request');
                }
            }
            echo json_encode($responses, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return;
        }

        $response = $this->handleOne($request);
        if ($response === null) {
            http_response_code(204);
            return;
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function handleOne(array $request): ?array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? null;
        $params = isset($request['params']) && is_array($request['params']) ? $request['params'] : [];

        if (($request['jsonrpc'] ?? '') !== '2.0' || !is_string($method)) {
            return $this->error($id, -32600, 'Invalid Request');
        }

        // JSON-RPC notification: no response required.
        if (!array_key_exists('id', $request)) {
            $this->auditLogger->record('notification_received', ['method' => $method]);
            return null;
        }

        try {
            return match ($method) {
                'initialize' => $this->result($id, $this->initializeResult()),
                'ping' => $this->result($id, new stdClass()),
                'tools/list' => $this->result($id, ['tools' => $this->tools->listTools()]),
                'tools/call' => $this->result($id, $this->callTool($params)),
                default => $this->error($id, -32601, 'Method not found', $method),
            };
        } catch (Throwable $e) {
            $this->auditLogger->record('mcp_method_failed', ['method' => $method, 'error' => $e->getMessage()]);
            return $this->error($id, -32603, 'Internal error', $e->getMessage());
        }
    }

    private function initializeResult(): array
    {
        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'capabilities' => [
                'tools' => ['listChanged' => false],
            ],
            'serverInfo' => [
                'name' => 'conta-mcp-server',
                'version' => '0.1.0',
            ],
        ];
    }

    private function callTool(array $params): array
    {
        $name = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException('tools/call requires params.name.');
        }
        if (!is_array($arguments)) {
            throw new InvalidArgumentException('tools/call params.arguments must be an object.');
        }

        $result = $this->tools->call($name, $arguments);
        $isError = !($result['ok'] ?? false);

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result['body'] ?? $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ],
            ],
            'isError' => $isError,
            'structuredContent' => [
                'status' => $result['status'] ?? null,
                'ok' => $result['ok'] ?? false,
                'data' => $result['body'] ?? null,
            ],
        ];
    }

    private function result(mixed $id, mixed $result): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    private function error(mixed $id, int $code, string $message, mixed $data = null): array
    {
        $error = ['code' => $code, 'message' => $message];
        if ($data !== null) {
            $error['data'] = $data;
        }
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => $error,
        ];
    }
}
